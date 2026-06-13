<?php

namespace App\Services;

use App\Models\BulkOrderToken;
use App\Models\WhatsappCart;
use App\Models\WhatsappContact;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use InvalidArgumentException;

class BulkOrderService
{
    public function __construct(
        private PlanFeatureService $planFeatures
    ) {}

    public function isAvailable(): bool
    {
        return $this->planFeatures->isBulkWebOrderAvailable();
    }

    public function minCartLines(): int
    {
        return max(1, (int) config('bulk_order.min_cart_lines', 3));
    }

    public function issueToken(WhatsappContact $contact): ?BulkOrderToken
    {
        if (!$this->isAvailable()) {
            return null;
        }

        BulkOrderToken::query()
            ->where('contact_id', $contact->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->delete();

        $hours = max(1, (int) config('bulk_order.token_ttl_hours', 24));

        return BulkOrderToken::create([
            'contact_id' => $contact->id,
            'token' => BulkOrderToken::generateToken(),
            'expires_at' => now()->addHours($hours),
        ]);
    }

    public function formUrl(BulkOrderToken $token): string
    {
        return URL::route('bulk-order.show', ['token' => $token->token]);
    }

    public function findValidToken(string $token): ?BulkOrderToken
    {
        $record = BulkOrderToken::query()
            ->where('token', $token)
            ->with('contact')
            ->first();

        return $record && $record->isValid() ? $record : null;
    }

    /**
     * @return array{categories: array<int, array<string, mixed>>, products: array<int, array<string, mixed>>}
     */
    public function catalogPayload(?int $categoryId = null, ?string $search = null): array
    {
        $categories = WhatsappMenuItem::catalogCategories()
            ->orderBy('order')
            ->get(['id', 'title', 'icon'])
            ->map(fn ($c) => [
                'id' => $c->id,
                'title' => $c->title,
                'icon' => $c->icon ?: '📦',
            ])
            ->values()
            ->all();

        $query = WhatsappPrice::query()
            ->where('is_active', true)
            ->with('menuCategory:id,title,icon');

        if ($categoryId) {
            $query->where('menu_item_id', $categoryId);
        }

        if ($search !== null && trim($search) !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $products = $query
            ->orderBy('name')
            ->limit(200)
            ->get()
            ->map(function (WhatsappPrice $p) {
                $unit = $p->is_promo && $p->promo_price ? (float) $p->promo_price : (float) $p->price;

                return [
                    'id' => $p->id,
                    'sku' => $p->sku,
                    'name' => $p->name,
                    'description' => $this->cleanText($p->description),
                    'measurements' => $this->productMeasurements($p),
                    'characteristics' => $this->productCharacteristics($p),
                    'category_id' => $p->menu_item_id,
                    'category' => $p->menuCategory?->title,
                    'price' => $unit,
                    'is_promo' => (bool) $p->is_promo,
                    'allow_quantity' => (bool) $p->allow_quantity_selection,
                    'min_qty' => max(1, (int) ($p->min_quantity ?? 1)),
                    'max_qty' => max(1, (int) ($p->max_quantity ?? 99)),
                    'image' => $p->image ? asset('storage/' . ltrim($p->image, '/')) : null,
                ];
            })
            ->values()
            ->all();

        return [
            'categories' => $categories,
            'products' => $products,
        ];
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, note?: string|null}>  $items
     */
    public function submitFromForm(BulkOrderToken $token, array $items, ?string $orderNote = null): WhatsappCart
    {
        if (!$token->isValid()) {
            throw new InvalidArgumentException('El enlace expiró o ya fue utilizado.');
        }

        $contact = $token->contact;
        if (!$contact) {
            throw new InvalidArgumentException('Cliente no encontrado.');
        }

        $cart = $this->submitForContact(
            $contact,
            $items,
            $orderNote,
            [
                'source' => 'bulk_web_form',
                'bulk_order_token_id' => $token->id,
                'submitted_at' => now()->toIso8601String(),
            ]
        );

        $token->markUsed($cart->id);

        return $cart;
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, note?: string|null}>  $items
     */
    public function submitFromAdmin(
        WhatsappContact $contact,
        array $items,
        ?string $orderNote,
        int $userId,
        bool $notifyWhatsapp = true
    ): WhatsappCart {
        $cart = $this->submitForContact(
            $contact,
            $items,
            $orderNote,
            [
                'source' => 'admin_web_form',
                'created_by_user_id' => $userId,
                'submitted_at' => now()->toIso8601String(),
            ]
        );

        if ($notifyWhatsapp) {
            $this->notifyContactViaWhatsapp($cart);
        }

        return $cart;
    }

    /**
     * @return array<int, array{id: int, name: string, phone: string|null}>
     */
    public function searchContacts(string $query, int $limit = 20): array
    {
        $term = trim($query);
        if (mb_strlen($term) < 2) {
            return [];
        }

        $like = '%' . $term . '%';

        return WhatsappContact::query()
            ->where('status', 'active')
            ->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('phone_number', 'like', $like)
                    ->orWhere('national_id', 'like', $like);
            })
            ->orderBy('name')
            ->limit(max(1, min(30, $limit)))
            ->get(['id', 'name', 'phone_number'])
            ->map(fn (WhatsappContact $c) => [
                'id' => $c->id,
                'name' => $c->name ?: 'Cliente',
                'phone' => $c->phone_number,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, note?: string|null}>  $items
     */
    public function submitForContact(
        WhatsappContact $contact,
        array $items,
        ?string $orderNote = null,
        array $metadata = []
    ): WhatsappCart {
        if ($items === []) {
            throw new InvalidArgumentException('Agrega al menos un producto.');
        }

        return DB::transaction(function () use ($contact, $items, $orderNote, $metadata) {
            WhatsappCart::query()
                ->where('contact_id', $contact->id)
                ->where('status', 'active')
                ->update(['status' => 'abandoned']);

            $cart = WhatsappCart::create([
                'contact_id' => $contact->id,
                'total' => 0,
                'status' => 'active',
                'note' => $orderNote,
                'metadata' => $metadata,
            ]);

            $total = 0;

            foreach ($items as $row) {
                $productId = (int) ($row['product_id'] ?? 0);
                $quantity = max(1, (int) ($row['quantity'] ?? 1));
                $lineNote = isset($row['note']) ? trim((string) $row['note']) : null;
                if ($lineNote === '') {
                    $lineNote = null;
                }

                $price = WhatsappPrice::query()
                    ->where('id', $productId)
                    ->where('is_active', true)
                    ->first();

                if (!$price) {
                    throw new InvalidArgumentException('Uno de los productos ya no está disponible.');
                }

                if ($price->allow_quantity_selection) {
                    $min = max(1, (int) ($price->min_quantity ?? 1));
                    $max = max($min, (int) ($price->max_quantity ?? 99));
                    $quantity = min($max, max($min, $quantity));
                } else {
                    $quantity = 1;
                }

                $unitPrice = $price->is_promo && $price->promo_price
                    ? (float) $price->promo_price
                    : (float) $price->price;

                $cart->items()->create([
                    'whatsapp_price_id' => $price->id,
                    'name' => $price->name,
                    'price' => $unitPrice,
                    'quantity' => $quantity,
                    'line_note' => $lineNote,
                ]);

                $total += $unitPrice * $quantity;
            }

            $cart->total = round($total, 2);
            $cart->save();

            WhatsappCart::query()
                ->where('contact_id', $contact->id)
                ->where('status', WhatsappCart::STATUS_PENDING)
                ->where('id', '!=', $cart->id)
                ->update(['status' => WhatsappCart::STATUS_CANCELLED]);

            $whatsapp = app(WhatsappService::class);
            $orderNumber = $whatsapp->finalizeBulkWebOrder($cart);
            $cart->refresh();
            $cart->setAttribute('order_number', $orderNumber);

            return $cart->load('items');
        });
    }

    public function notifyContactViaWhatsapp(WhatsappCart $cart): void
    {
        try {
            $cart->loadMissing('contact');
            app(OrderConfirmationService::class)->notifyBulkOrderSubmitted($cart->contact, $cart);
        } catch (\Throwable $e) {
            Log::error('[BulkOrder] No se pudo notificar por WhatsApp', [
                'cart_id' => $cart->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $text !== '' ? $text : null;
    }

    private function productMeasurements(WhatsappPrice $product): ?string
    {
        $parts = array_values(array_filter([
            $this->cleanText($product->quantity ?? null),
            $this->cleanText($product->format ?? null),
            $this->cleanText($product->flavor ?? null),
        ]));

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    /**
     * @return array<int, string>
     */
    private function productCharacteristics(WhatsappPrice $product): array
    {
        $raw = $product->characteristics;
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $items = is_array($decoded) ? $decoded : preg_split('/\r\n|\r|\n/', $raw);
        } else {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($item) => $this->cleanText(is_string($item) ? $item : (string) $item),
            $items
        )));
    }
}
