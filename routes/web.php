<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [App\Http\Controllers\AdminController::class, 'orders'])->name('orders');
    Route::get('/messages', [App\Http\Controllers\AdminController::class, 'messages'])->name('messages');
    Route::get('/orders/{id}/details', [App\Http\Controllers\AdminController::class, 'orderDetails'])->name('orders.details');
    Route::get('/chats', [App\Http\Controllers\AdminController::class, 'chats'])->name('chats');
    Route::get('/chats/{contact}', [App\Http\Controllers\AdminController::class, 'chat'])->name('chat');
    Route::get('/chats/{contact}/messages', [App\Http\Controllers\AdminController::class, 'chat'])->name('chat.messages');
    Route::get('/chats/{contact}/new-messages', [App\Http\Controllers\AdminController::class, 'getNewMessages'])->name('chat.new-messages');
    Route::post('/chats/send', [App\Http\Controllers\AdminController::class, 'sendMessage'])->name('chat.send');
    Route::get('/messages/{message}/image', [App\Http\Controllers\AdminController::class, 'getImage'])->name('message.image');
    Route::get('/contacts/{id}', [App\Http\Controllers\AdminController::class, 'contactDetails']);
    Route::post('/contacts/{contact}/toggle-bot', [App\Http\Controllers\AdminController::class, 'toggleBot'])->name('contact.toggle-bot');

    // Rutas para la gestión del chatbot
    // Menús y Categorías
    Route::get('/menus', [App\Http\Controllers\Admin\ChatbotController::class, 'menus'])->name('menus.index');
    Route::post('/menus', [App\Http\Controllers\Admin\ChatbotController::class, 'storeMenu'])->name('menus.store');
    Route::put('/menus/{menu}', [App\Http\Controllers\Admin\ChatbotController::class, 'updateMenu'])->name('menus.update');
    Route::delete('/menus/{menu}', [App\Http\Controllers\Admin\ChatbotController::class, 'deleteMenu'])->name('menus.delete');

    // Items de Menú
    Route::post('/menu-items', [App\Http\Controllers\Admin\ChatbotController::class, 'storeMenuItem'])->name('menu-items.store');
    Route::put('/menu-items/{item}', [App\Http\Controllers\Admin\ChatbotController::class, 'updateMenuItem'])->name('menu-items.update');
    Route::delete('/menu-items/{item}', [App\Http\Controllers\Admin\ChatbotController::class, 'deleteMenuItem'])->name('menu-items.delete');

    // Productos
    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'show'])->name('products.show');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');

    // Configuración del Chatbot
    Route::get('/chatbot/config', [App\Http\Controllers\Admin\ChatbotController::class, 'config'])->name('chatbot.config');
    Route::put('/chatbot/config', [App\Http\Controllers\Admin\ChatbotController::class, 'updateConfig'])->name('chatbot.config.update');
});

// Rutas de autenticación
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::post('/admin/orders/{id}/status', [App\Http\Controllers\AdminController::class, 'updateOrderStatus']);
