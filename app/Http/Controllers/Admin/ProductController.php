<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = WhatsappPrice::with(['menuCategory:id,title,description,icon'])
            ->orderBy('name')
            ->get();

        $categories = WhatsappMenuItem::catalogCategories()
            ->where('is_active', true)
            ->select('id', 'title', 'description', 'icon')
            ->orderBy('order')
            ->get();

        $stats = WhatsappPrice::summaryStats();

        return view('admin.products.index', compact('products', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = $this->getCategories();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAndPrepare($request);

        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        $product = WhatsappPrice::create($data);

        return response()->json([
            'message' => 'Producto creado correctamente',
            'product' => $this->formatProduct($product->load('menuCategory')),
        ]);
    }

    public function show(WhatsappPrice $product)
    {
        return response()->json($this->formatProduct($product->load('menuCategory')));
    }

    public function edit(WhatsappPrice $product)
    {
        $categories = $this->getCategories();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, WhatsappPrice $product)
    {
        $data = $this->validateAndPrepare($request, $product);

        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'product' => $this->formatProduct($product->fresh()->load('menuCategory')),
        ]);
    }

    public function destroy(WhatsappPrice $product)
    {
        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }

    private function getCategories()
    {
        return WhatsappMenuItem::catalogCategories()
            ->where('is_active', true)
            ->orderBy('order')
            ->get();
    }

    private function validateAndPrepare(Request $request, ?WhatsappPrice $product = null)
    {
        $productId = $product?->id;

        $validator = Validator::make($request->all(), [
            'sku' => [
                'required',
                'string',
                'max:4',
                Rule::unique('whatsapp_prices', 'sku')->ignore($productId),
            ],
            'name' => 'required|string|max:255',
            'menu_item_id' => 'required|exists:whatsapp_menu_items,id',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0|lt:price',
            'description' => 'nullable|string|max:5000',
            'benefits' => 'nullable|string|max:5000',
            'characteristics' => 'nullable|string|max:5000',
            'stock' => 'nullable|integer|min:0',
            'allow_quantity_selection' => 'nullable|boolean',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();
        $category = WhatsappMenuItem::findOrFail($validated['menu_item_id']);

        $promoPrice = isset($validated['promo_price']) && $validated['promo_price'] !== ''
            ? (float) $validated['promo_price']
            : null;

        $minQty = (int) ($validated['min_quantity'] ?? 1);
        $maxQty = (int) ($validated['max_quantity'] ?? 999);

        if ($maxQty < $minQty) {
            return response()->json([
                'errors' => ['max_quantity' => ['La cantidad máxima debe ser mayor o igual a la mínima.']],
            ], 422);
        }

        return [
            'menu_item_id' => $category->id,
            'category' => $category->title,
            'sku' => strtoupper(trim($validated['sku'])),
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'benefits' => $validated['benefits'] ?? null,
            'characteristics' => $this->parseCharacteristics($validated['characteristics'] ?? ''),
            'price' => (float) $validated['price'],
            'promo_price' => $promoPrice,
            'is_promo' => $promoPrice !== null && $promoPrice > 0,
            'promo_start_date' => ($promoPrice !== null && $promoPrice > 0) ? now()->toDateString() : null,
            'promo_end_date' => ($promoPrice !== null && $promoPrice > 0) ? now()->addDays(30)->toDateString() : null,
            'currency' => 'USD',
            'is_active' => $request->boolean('is_active'),
            'stock' => (int) ($validated['stock'] ?? 0),
            'allow_quantity_selection' => $request->boolean('allow_quantity_selection', true),
            'min_quantity' => $minQty,
            'max_quantity' => $maxQty,
        ];
    }

    private function parseCharacteristics(?string $text): array
    {
        if (!$text) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $text);

        return array_values(array_filter(array_map('trim', $lines)));
    }

    private function characteristicsToText($characteristics): string
    {
        if (empty($characteristics)) {
            return '';
        }

        if (is_string($characteristics)) {
            $decoded = json_decode($characteristics, true);
            $characteristics = is_array($decoded) ? $decoded : [$characteristics];
        }

        if (!is_array($characteristics)) {
            return '';
        }

        return implode("\n", $characteristics);
    }

    private function formatProduct(WhatsappPrice $product): array
    {
        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'name' => $product->name,
            'menu_item_id' => $product->menu_item_id,
            'category' => $product->category,
            'category_title' => $product->menuCategory?->title ?? $product->category,
            'description' => $product->description,
            'benefits' => $product->benefits,
            'characteristics' => $this->characteristicsToText($product->characteristics),
            'price' => $product->price,
            'promo_price' => $product->promo_price,
            'is_promo' => $product->is_promo,
            'is_active' => (bool) $product->is_active,
            'stock' => $product->stock,
            'allow_quantity_selection' => (bool) $product->allow_quantity_selection,
            'min_quantity' => $product->min_quantity,
            'max_quantity' => $product->max_quantity,
            'icon' => $product->icon,
        ];
    }
}
