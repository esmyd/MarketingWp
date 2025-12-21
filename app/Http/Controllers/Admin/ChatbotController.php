<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use App\Models\WhatsappChatbotConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    /**
     * Muestra la vista de gestión de menús y categorías
     */
    public function menus()
    {
        $menus = WhatsappMenu::where('type', 'category')->with('items')->orderBy('order')->get();
        return view('admin.menus.index', compact('menus'));
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
            'business_name' => 'required|string|max:255',
            'business_description' => 'nullable|string',
            'business_hours' => 'nullable|string|max:255',
            'welcome_message' => 'required|string',
            'menu_message' => 'required|string',
            'menu_command' => 'required|string|max:50',
            'help_command' => 'required|string|max:50',
            'offline_message' => 'nullable|string',
            'error_message' => 'nullable|string',
            'not_found_message' => 'nullable|string',
            'order_confirmation_message' => 'nullable|string',
            'order_status_message' => 'nullable|string',
            'payment_confirmation_message' => 'nullable|string',
        ]);

        $config = WhatsappChatbotConfig::first();
        if (!$config) {
            $config = new WhatsappChatbotConfig();
        }

        $config->fill($validated);
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
     * Crea un nuevo item de menú
     */
    public function storeMenuItem(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:whatsapp_menus,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_id' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $item = new WhatsappMenuItem($validated);
        $item->save();

        return redirect()->back()->with('success', 'Item creado correctamente');
    }

    /**
     * Actualiza un item de menú existente
     */
    public function updateMenuItem(Request $request, WhatsappMenuItem $item)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'action_id' => 'required|string|max:50',
            'icon' => 'nullable|string|max:50',
            'order' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', 'Item actualizado correctamente');
    }

    /**
     * Elimina un item de menú
     */
    public function deleteMenuItem(WhatsappMenuItem $item)
    {
        $item->delete();
        return response()->json(['message' => 'Item eliminado correctamente']);
    }
}
