<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use App\Models\WhatsappChatbotConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ChatbotController extends Controller
{
    /**
     * Gestión de categorías del catálogo (items del menú prices_menu).
     */
    public function menus()
    {
        $categories = WhatsappMenuItem::catalogCategories()
            ->withCount([
                'prices',
                'prices as active_prices_count' => fn ($query) => $query->where('is_active', true),
            ])
            ->orderBy('order')
            ->orderBy('title')
            ->get();

        $productStats = WhatsappPrice::summaryStats();

        $stats = [
            'total' => $categories->count(),
            'active' => $categories->where('is_active', true)->count(),
            'with_products' => $categories->where('prices_count', '>', 0)->count(),
            'empty' => $categories->where('prices_count', 0)->count(),
            'products_total' => $productStats['total'],
            'products_active' => $productStats['active'],
            'products_unassigned' => $productStats['total'] - WhatsappPrice::inCatalogCategoriesCount(),
        ];

        return view('admin.menus.index', compact('categories', 'stats'));
    }

    /**
     * Devuelve una categoría en JSON (edición AJAX).
     */
    public function showMenuItem(WhatsappMenuItem $item)
    {
        $this->ensureCatalogCategory($item);

        return response()->json($this->formatCategory($item->loadCount('prices')));
    }

    /**
     * Muestra la vista de gestión de productos
     */
    public function products()
    {
        $products = WhatsappPrice::with('category')->orderBy('sku')->get();
        $categories = WhatsappMenu::where('type', 'category')->get();
        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Muestra la vista de configuración del chatbot
     */
    public function config()
    {
        $config = WhatsappChatbotConfig::first();
        return view('admin.chatbot.config', compact('config'));
    }

    /**
     * Actualiza la configuración del chatbot
     */
    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'nullable|string|max:255',
            'business_description' => 'nullable|string',
            'business_hours' => 'nullable|string|max:255',
            'welcome_message' => 'nullable|string',
            'menu_message' => 'nullable|string',
            'menu_command' => 'nullable|string|max:50',
            'help_command' => 'nullable|string|max:50',
            'offline_message' => 'nullable|string',
            'error_message' => 'nullable|string',
            'not_found_message' => 'nullable|string',
            'order_confirmation_message' => 'nullable|string',
            'order_status_message' => 'nullable|string',
            'payment_confirmation_message' => 'nullable|string',
            'monitoring_phone_number' => 'nullable|string|max:20',
            'monitoring_email' => 'nullable|email|max:255',
        ]);

        $config = WhatsappChatbotConfig::first();
        if (!$config) {
            $config = new WhatsappChatbotConfig();
            // Si no existe, necesitamos un business_profile_id
            $businessProfile = \App\Models\WhatsappBusinessProfile::first();
            if ($businessProfile) {
                $config->business_profile_id = $businessProfile->id;
            }
        }

        $config->fill($validated);
        // Checkbox: si no viene en el POST, debe guardarse como false (no dejar el valor anterior)
        $config->monitoring_enabled = $request->has('monitoring_enabled')
            && $request->input('monitoring_enabled') == '1';
        $config->save();

        return redirect()->back()->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Crea un nuevo menú
     */
    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:button,list,text',
            'content' => 'required|string',
            'button_text' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'action_id' => 'required|string|max:50',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $menu = new WhatsappMenu($validated);
        $menu->save();

        return redirect()->back()->with('success', 'Menú creado correctamente');
    }

    /**
     * Actualiza un menú existente
     */
    public function updateMenu(Request $request, WhatsappMenu $menu)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:button,list,text',
            'content' => 'required|string',
            'button_text' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:50',
            'action_id' => 'required|string|max:50',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $menu->update($validated);

        return redirect()->back()->with('success', 'Menú actualizado correctamente');
    }

    /**
     * Elimina un menú
     */
    public function deleteMenu(WhatsappMenu $menu)
    {
        DB::transaction(function () use ($menu) {
            // Eliminar items asociados
            $menu->items()->delete();
            // Eliminar menú
            $menu->delete();
        });

        return response()->json(['message' => 'Menú eliminado correctamente']);
    }

    /**
     * Crea un nuevo producto
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:whatsapp_prices,sku',
            'name' => 'required|string|max:255',
            'menu_item_id' => 'required|exists:whatsapp_menus,id',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'nutritional_info' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $product = new WhatsappPrice($validated);
        $product->save();

        return redirect()->back()->with('success', 'Producto creado correctamente');
    }

    /**
     * Actualiza un producto existente
     */
    public function updateProduct(Request $request, WhatsappPrice $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:whatsapp_prices,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'menu_item_id' => 'required|exists:whatsapp_menus,id',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'nutritional_info' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return redirect()->back()->with('success', 'Producto actualizado correctamente');
    }

    /**
     * Elimina un producto
     */
    public function deleteProduct(WhatsappPrice $product)
    {
        $product->delete();
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }

    /**
     * Crea una categoría del catálogo.
     */
    public function storeMenuItem(Request $request)
    {
        $pricesMenu = $this->getPricesMenu();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $title = trim($validated['title']);
        $order = $validated['order'] ?? ((int) WhatsappMenuItem::catalogCategories()->max('order') + 1);

        $item = WhatsappMenuItem::create([
            'menu_id' => $pricesMenu->id,
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'action_id' => $this->makeUniqueActionId($pricesMenu->id, $title),
            'icon' => $validated['icon'] ?? '📦',
            'order' => $order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json([
            'message' => 'Categoría creada correctamente',
            'category' => $this->formatCategory($item->loadCount('prices')),
        ]);
    }

    /**
     * Actualiza una categoría del catálogo.
     */
    public function updateMenuItem(Request $request, WhatsappMenuItem $item)
    {
        $this->ensureCatalogCategory($item);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $title = trim($validated['title']);

        $item->update([
            'title' => $title,
            'description' => $validated['description'] ?? null,
            'icon' => $validated['icon'] ?? $item->icon ?? '📦',
            'order' => $validated['order'] ?? $item->order,
            'is_active' => $request->boolean('is_active'),
        ]);

        WhatsappPrice::where('menu_item_id', $item->id)->update(['category' => $title]);

        return response()->json([
            'message' => 'Categoría actualizada correctamente',
            'category' => $this->formatCategory($item->fresh()->loadCount('prices')),
        ]);
    }

    /**
     * Elimina una categoría (solo si no tiene productos).
     */
    public function deleteMenuItem(WhatsappMenuItem $item)
    {
        $this->ensureCatalogCategory($item);

        if ($item->prices()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar: la categoría tiene productos asociados. Desactívala o mueve los productos primero.',
            ], 422);
        }

        $item->delete();

        return response()->json(['message' => 'Categoría eliminada correctamente']);
    }

    private function getPricesMenu(): WhatsappMenu
    {
        $menu = WhatsappMenu::where('action_id', 'prices_menu')->first();

        if (!$menu) {
            abort(500, 'No está configurado el menú de catálogo (prices_menu).');
        }

        return $menu;
    }

    private function ensureCatalogCategory(WhatsappMenuItem $item): void
    {
        $pricesMenu = $this->getPricesMenu();

        if ((int) $item->menu_id !== (int) $pricesMenu->id) {
            abort(404);
        }
    }

    private function makeUniqueActionId(int $menuId, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title, '_') ?: 'categoria';
        $base = Str::limit($base, 40, '');
        $actionId = $base;
        $suffix = 1;

        while (
            WhatsappMenuItem::where('menu_id', $menuId)
                ->where('action_id', $actionId)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $actionId = $base . '_' . $suffix;
            $suffix++;
        }

        return $actionId;
    }

    private function formatCategory(WhatsappMenuItem $item): array
    {
        if (!isset($item->prices_count)) {
            $item->loadCount([
                'prices',
                'prices as active_prices_count' => fn ($query) => $query->where('is_active', true),
            ]);
        }

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'icon' => $item->icon,
            'order' => $item->order,
            'is_active' => (bool) $item->is_active,
            'action_id' => $item->action_id,
            'products_count' => (int) ($item->prices_count ?? 0),
            'active_products_count' => (int) ($item->active_prices_count ?? 0),
        ];
    }
}
