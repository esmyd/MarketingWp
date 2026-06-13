<?php

namespace App\Services;

use App\Enums\MarketingStepKey;
use App\Models\MarketingFlow;
use App\Models\MarketingFlowStep;
use App\Models\WhatsappBusinessProfile;
use App\Models\WhatsappChatbotConfig;
use App\Models\WhatsappContact;
use App\Models\WhatsappMenu;
use App\Services\Whatsapp\WhatsappMessagePayload;
use Illuminate\Support\Str;

class MarketingCatalogBuilder
{
    public function __construct(
        protected MarketingFlowPayloadBuilder $payloadBuilder,
        protected DemoClienteService $demoCliente,
        protected ?WhatsappBusinessProfile $businessProfile = null,
    ) {
        if (!$this->businessProfile?->id) {
            $this->businessProfile = WhatsappBusinessProfile::first();
        }
    }

    public function buildCatalog(?WhatsappContact $contact = null, ?int $categoryId = null): array
    {
        $step = $this->getProductsStep();

        if (!$step || !$step->is_enabled) {
            return $this->disabledPayload($step, $contact);
        }

        $vars = $this->variables($contact);
        $type = $step->getInteractiveType();

        if ($type === 'text') {
            return WhatsappMessagePayload::text($step->renderMessage($vars));
        }

        if (in_array($type, ['button', 'flow', 'cta_url'], true)) {
            return $this->payloadBuilder->build($step, $vars);
        }

        $source = $step->config['catalog_source'] ?? 'products';

        if ($source === 'manual') {
            return $this->payloadBuilder->build($step, $vars);
        }

        if ($source === 'categories' && $categoryId === null) {
            return $this->buildCategoryListPayload($step, $vars);
        }

        return $this->buildProductListPayload($step, $vars, $categoryId);
    }

    protected function getProductsStep(): ?MarketingFlowStep
    {
        if (!$this->businessProfile) {
            return null;
        }

        $flow = MarketingFlow::query()
            ->where('business_profile_id', $this->businessProfile->id)
            ->where('is_active', true)
            ->where('is_default', true)
            ->with('steps')
            ->first();

        return $flow?->steps->firstWhere('step_key', MarketingStepKey::PRODUCTS_MENU);
    }

    protected function variables(?WhatsappContact $contact): array
    {
        $menu = WhatsappMenu::where('action_id', 'prices_menu')->first();
        $categories = $menu
            ? $this->demoCliente->applyCategoryScope(
                $menu->items()->where('is_active', true)
            )->count()
            : 0;
        $products = $menu
            ? $this->demoCliente->applyCategoryScope(
                $menu->items()->where('is_active', true)
            )->get()->sum(
                fn ($item) => $this->demoCliente->applyProductScope(
                    $item->prices()->where('is_active', true)
                )->count()
            )
            : 0;

        $meta = is_array($this->businessProfile?->metadata) ? $this->businessProfile->metadata : [];

        return [
            'nombre' => $contact?->name ?? 'Cliente',
            'nombre_bot' => WhatsappChatbotConfig::where('business_profile_id', $this->businessProfile?->id)->first()?->bot_name
                ?: 'Asistente virtual',
            'nombre_empresa' => $this->businessProfile?->business_name ?? 'Tienda',
            'telefono_soporte' => $meta['whatsapp']
                ?? $this->businessProfile?->phone_number
                ?? config('whatsapp.demo_whatsapp_number', ''),
            'horario_atencion' => $meta['business_hours'] ?? 'Lunes a viernes 9:00 - 18:00',
            'total_productos' => (string) $products,
            'total_categorias' => (string) $categories,
            'total' => '0.00',
            'moneda' => 'USD',
            'cantidad_items' => '0',
            'numero_pedido' => '-',
            'estado_pedido' => '-',
        ];
    }

    protected function disabledPayload(?MarketingFlowStep $step, ?WhatsappContact $contact): array
    {
        $message = $step?->message_template
            ? MarketingFlowStep::interpolate($step->message_template, $this->variables($contact))
            : 'Lo siento, el catálogo de productos no está disponible en este momento.';

        return WhatsappMessagePayload::text($message);
    }

    protected function buildCategoryListPayload(MarketingFlowStep $step, array $vars): array
    {
        $menu = WhatsappMenu::where('action_id', 'prices_menu')->first();
        if (!$menu) {
            return WhatsappMessagePayload::text('Lo siento, el catálogo no está configurado.');
        }

        $menuItems = $menu->items()
            ->where('is_active', true)
            ->when(true, fn ($q) => $this->demoCliente->applyCategoryScope($q))
            ->orderBy('order')
            ->get();
        if ($menuItems->isEmpty()) {
            return WhatsappMessagePayload::text('No hay categorías de productos disponibles.');
        }

        $rows = [];
        foreach ($menuItems as $menuItem) {
            $activeCount = $this->demoCliente->applyProductScope(
                $menuItem->prices()->where('is_active', true)
            )->count();
            if ($activeCount === 0) {
                continue;
            }

            $rows[] = [
                'id' => 'cat_' . $menuItem->id,
                'title' => Str::limit($menuItem->title, 24, ''),
                'description' => Str::limit($menuItem->description ?: "{$activeCount} producto(s)", 72, ''),
            ];
        }

        if ($rows === []) {
            return WhatsappMessagePayload::text('No hay productos activos en el catálogo.');
        }

        $sections = [['title' => 'Categorías', 'rows' => array_slice($rows, 0, 10)]];
        $sections = $this->appendConfiguredSections($step, $sections, $vars);

        return $this->composeListPayload($step, $vars, $sections);
    }

    protected function buildProductListPayload(MarketingFlowStep $step, array $vars, ?int $categoryId = null): array
    {
        $menu = WhatsappMenu::where('action_id', 'prices_menu')->first();
        if (!$menu) {
            return WhatsappMessagePayload::text('Lo siento, el catálogo no está configurado.');
        }

        $maxRows = max(1, min(8, (int) ($step->config['max_product_rows'] ?? 8)));
        $menuItems = $menu->items()
            ->where('is_active', true)
            ->when(true, fn ($q) => $this->demoCliente->applyCategoryScope($q))
            ->orderBy('order');

        if ($categoryId) {
            $menuItems->where('id', $categoryId);
        }

        $menuItems = $menuItems->get();

        if ($menuItems->isEmpty()) {
            return WhatsappMessagePayload::text('No hay productos disponibles en esta categoría.');
        }

        $sections = [];
        $totalRows = 0;

        foreach ($menuItems as $menuItem) {
            $prices = $this->demoCliente->applyProductScope(
                $menuItem->prices()->where('is_active', true)
            )
                ->orderBy('name')
                ->get();

            if ($prices->isEmpty()) {
                continue;
            }

            $rows = [];
            foreach ($prices as $price) {
                $rows[] = $this->formatProductRow($price);
                $totalRows++;
                if ($totalRows >= $maxRows) {
                    break 2;
                }
            }

            if ($rows !== []) {
                $sections[] = [
                    'title' => Str::limit($menuItem->title, 24, ''),
                    'rows' => $rows,
                ];
            }
        }

        if ($sections === []) {
            return WhatsappMessagePayload::text('No hay productos activos en el catálogo.');
        }

        $sections = $this->appendConfiguredSections($step, $sections, $vars);

        return $this->composeListPayload($step, $vars, $sections);
    }

    protected function formatProductRow($price): array
    {
        $title = Str::limit("[{$price->sku}] " . $price->name, 24, '');
        $priceText = $price->is_promo
            ? '💰 $' . number_format($price->promo_price, 2) . ' (Oferta)'
            : '💰 $' . number_format($price->price, 2);

        $maxDescLength = max(10, 72 - mb_strlen($priceText) - 3);
        $description = Str::limit($price->description ?? '', $maxDescLength, '...');
        $fullDescription = $description !== ''
            ? $priceText . ' - ' . $description
            : $priceText;

        if (mb_strlen($fullDescription) > 72) {
            $fullDescription = Str::limit($fullDescription, 72, '...');
        }

        return [
            'id' => (string) $price->id,
            'title' => $title,
            'description' => $fullDescription,
        ];
    }

    protected function appendConfiguredSections(MarketingFlowStep $step, array $sections, array $vars): array
    {
        foreach ($step->getListConfig()['sections'] ?? [] as $section) {
            $rows = [];
            foreach ($section['rows'] ?? [] as $row) {
                $rows[] = [
                    'id' => (string) ($row['id'] ?? ''),
                    'title' => Str::limit((string) ($row['title'] ?? ''), 24, ''),
                    'description' => !empty($row['description'])
                        ? Str::limit((string) $row['description'], 72, '')
                        : null,
                ];
            }
            if ($rows !== []) {
                $sections[] = [
                    'title' => Str::limit((string) ($section['title'] ?? 'Opciones'), 24, ''),
                    'rows' => $rows,
                ];
            }
        }

        if ($step->config['include_navigation'] ?? true) {
            $sections[] = [
                'title' => 'Navegación',
                'rows' => [
                    [
                        'id' => 'ver_mas_precios',
                        'title' => 'Ver más productos',
                        'description' => 'Lista completa de productos',
                    ],
                    [
                        'id' => 'menu_principal',
                        'title' => 'Menú principal',
                        'description' => 'Volver al inicio',
                    ],
                ],
            ];
        }

        $rowCount = 0;
        $trimmed = [];
        foreach ($sections as $section) {
            $rows = [];
            foreach ($section['rows'] ?? [] as $row) {
                if ($rowCount >= 10) {
                    break 2;
                }
                $rows[] = $row;
                $rowCount++;
            }
            if ($rows !== []) {
                $trimmed[] = ['title' => $section['title'], 'rows' => $rows];
            }
        }

        return $trimmed;
    }

    protected function composeListPayload(MarketingFlowStep $step, array $vars, array $sections): array
    {
        $body = $step->renderMessage($vars);
        if ($body === '') {
            $body = "🛍️ *Catálogo de productos*\n\nSelecciona un producto para ver más detalles.\nTambién puedes escribir el código SKU directamente.";
        }

        $listButton = $step->getListConfig()['button'] ?? 'Ver productos';
        $header = $step->getRenderedHeader($vars);
        $footer = $step->getRenderedFooter($vars);

        if ($step->getHeaderMode() === 'default') {
            $header = [
                'type' => 'text',
                'text' => $vars['nombre_empresa'] ?? 'Catálogo',
            ];
        }

        return WhatsappMessagePayload::list($body, $listButton, $sections, $header, $footer);
    }
}
