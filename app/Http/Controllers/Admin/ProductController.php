<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappPrice;
use App\Models\WhatsappMenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = WhatsappPrice::with(['category' => function($query) {
            $query->select('id', 'title', 'description', 'icon')
                  ->where('is_active', true);
        }])->orderBy('sku')->get();

        $categories = WhatsappMenuItem::where('is_active', true)
            ->whereNull('parent_id')
            ->select('id', 'title', 'description', 'icon')
            ->orderBy('order')
            ->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        $categories = WhatsappMenuItem::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:50|unique:whatsapp_prices,sku',
            'name' => 'required|string|max:255',
            'menu_item_id' => 'required|exists:whatsapp_menu_items,id',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'nutritional_info' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);

        $product = new WhatsappPrice($data);
        $product->save();

        return response()->json([
            'message' => 'Producto creado correctamente',
            'product' => $product
        ]);
    }

    /**
     * Display the specified product.
     */
    public function show(WhatsappPrice $product)
    {
        return response()->json($product->load('category'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(WhatsappPrice $product)
    {
        $categories = WhatsappMenuItem::where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, WhatsappPrice $product)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:50|unique:whatsapp_prices,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'menu_item_id' => 'required|exists:whatsapp_menu_items,id',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'nutritional_info' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);

        $product->update($data);

        return response()->json([
            'message' => 'Producto actualizado correctamente',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(WhatsappPrice $product)
    {
        $product->delete();
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
