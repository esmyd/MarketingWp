<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use App\Services\DemoClienteService;
use App\Services\PlanLimitsService;
use App\Services\ProductImageService;
use App\Services\ProductImportExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct(
        private readonly PlanLimitsService $planLimits,
        private readonly DemoClienteService $demoCliente,
        private readonly ProductImportExportService $productImportExport,
        private readonly ProductImageService $productImages,
    ) {}

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
        $planLimits = $this->planLimits->snapshot();

        return view('admin.products.index', compact('products', 'categories', 'stats', 'planLimits') + [
            'demoClienteOptions' => $this->demoCliente->options(),
            'activeDemoCliente' => $this->demoCliente->activeKey(),
        ]);
    }

    public function create()
    {
        $categories = $this->getCategories();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!$this->planLimits->canCreateProduct()) {
            return response()->json([
                'message' => $this->planLimits->productLimitMessage(),
            ], 422);
        }

        $data = $this->validateAndPrepare($request);

        if ($data instanceof \Illuminate\Http\JsonResponse) {
            return $data;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $this->productImages->store($request->file('image'));
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

        if ($request->boolean('remove_image')) {
            $this->productImages->delete($product->image);
            $data['image'] = null;
        } elseif ($request->hasFile('image')) {
            $data['image'] = $this->productImages->store($request->file('image'), $product->image);
        }

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'product' => $this->formatProduct($product->fresh()->load('menuCategory')),
        ]);
    }

    public function destroy(WhatsappPrice $product)
    {
        $this->productImages->delete($product->image);
        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }

    public function downloadImportTemplate()
    {
        return $this->productImportExport->templateDownloadResponse();
    }

    public function exportCatalog()
    {
        return $this->productImportExport->exportDownloadResponse();
    }

    public function importCatalog(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'mode' => 'nullable|in:upsert,create,update',
        ]);

        $result = $this->productImportExport->importFromUpload(
            $request->file('file'),
            $request->input('mode', 'upsert')
        );

        $total = $result['created'] + $result['updated'];
        $message = $total > 0
            ? "Importación completada: {$result['created']} creados, {$result['updated']} actualizados."
            : 'No se importó ningún producto.';

        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} fila(s) omitida(s).";
        }

        return response()->json([
            'message' => $message,
            'result' => $result,
        ], $total > 0 ? 200 : 422);
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
                'max:20',
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
            'demo_cliente' => 'nullable|string|max:64',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_image' => 'nullable|boolean',
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
            'demo_cliente' => isset($validated['demo_cliente']) && trim((string) $validated['demo_cliente']) !== ''
                ? trim((string) $validated['demo_cliente'])
                : ($category->demo_cliente ?: null),
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
            'demo_cliente' => $product->demo_cliente,
            'stock' => $product->stock,
            'allow_quantity_selection' => (bool) $product->allow_quantity_selection,
            'min_quantity' => $product->min_quantity,
            'max_quantity' => $product->max_quantity,
            'icon' => $product->icon,
            'image' => $product->image,
            'image_url' => $product->image_url,
        ];
    }
}
