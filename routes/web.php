<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/planes', [App\Http\Controllers\PricingController::class, 'index'])->name('pricing.index');
Route::get('/planes/contratar/{plan}', [App\Http\Controllers\PricingController::class, 'checkout'])->name('pricing.checkout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view,dashboard.menu')
        ->name('dashboard');

    Route::get('/orders', [App\Http\Controllers\AdminController::class, 'orders'])
        ->middleware(['permission:orders.view,orders.menu', 'platform.feature:orders'])
        ->name('orders');
    Route::get('/messages', [App\Http\Controllers\AdminController::class, 'messages'])
        ->middleware(['permission:chats.view,chats.menu', 'platform.feature:chat'])
        ->name('messages');
    Route::get('/orders/{id}/details', [App\Http\Controllers\AdminController::class, 'orderDetails'])
        ->middleware(['permission:orders.view,orders.menu', 'platform.feature:orders'])
        ->name('orders.details');
    Route::put('/orders/{id}', [App\Http\Controllers\AdminController::class, 'updateOrder'])
        ->middleware(['permission:orders.update', 'platform.feature:orders'])
        ->name('orders.update');
    Route::post('/orders/{id}/notes', [App\Http\Controllers\AdminController::class, 'storeOrderNote'])
        ->middleware(['permission:orders.update', 'platform.feature:orders'])
        ->name('orders.notes.store');
    Route::get('/chats', [App\Http\Controllers\AdminController::class, 'chats'])
        ->middleware(['permission:chats.view,chats.menu', 'platform.feature:chat'])
        ->name('chats');

    Route::get('/clients', [App\Http\Controllers\Admin\ClientController::class, 'index'])
        ->middleware('permission:clients.view,clients.menu')
        ->name('clients.index');
    Route::get('/clients/{client}', [App\Http\Controllers\Admin\ClientController::class, 'show'])
        ->middleware('permission:clients.detail,clients.view')
        ->name('clients.show');
    Route::put('/clients/{client}', [App\Http\Controllers\Admin\ClientController::class, 'update'])
        ->middleware('permission:clients.update')
        ->name('clients.update');
    Route::post('/clients/{client}/notes', [App\Http\Controllers\Admin\ClientController::class, 'storeNote'])
        ->middleware('permission:clients.notes')
        ->name('clients.notes.store');

    Route::get('/chats/{contact}', [App\Http\Controllers\AdminController::class, 'chat'])
        ->middleware(['permission:chats.open,chats.view', 'platform.feature:chat'])
        ->name('chat');
    Route::get('/chats/{contact}/messages', [App\Http\Controllers\AdminController::class, 'chat'])
        ->middleware(['permission:chats.open,chats.view', 'platform.feature:chat'])
        ->name('chat.messages');
    Route::get('/chats/{contact}/new-messages', [App\Http\Controllers\AdminController::class, 'getNewMessages'])
        ->middleware(['permission:chats.open,chats.view', 'platform.feature:chat'])
        ->name('chat.new-messages');
    Route::get('/chats/list/update', [App\Http\Controllers\AdminController::class, 'getContactsList'])
        ->middleware(['permission:chats.view,chats.menu', 'platform.feature:chat'])
        ->name('chat.contacts.update');
    Route::get('/agent-requests/poll', [App\Http\Controllers\AdminController::class, 'pollAgentRequests'])
        ->middleware(['permission:chats.view,chats.menu', 'platform.feature:chat'])
        ->name('agent-requests.poll');
    Route::post('/chats/send', [App\Http\Controllers\AdminController::class, 'sendMessage'])
        ->middleware(['permission:chats.send', 'platform.feature:chat'])
        ->name('chat.send');
    Route::post('/chats/typing', [App\Http\Controllers\AdminController::class, 'typingIndicator'])
        ->middleware(['permission:chats.send', 'platform.feature:chat'])
        ->name('chat.typing');
    Route::get('/messages/{message}/image', [App\Http\Controllers\AdminController::class, 'getImage'])
        ->middleware(['permission:chats.view,chats.open', 'platform.feature:chat'])
        ->name('message.image');
    Route::get('/contacts/{id}', [App\Http\Controllers\AdminController::class, 'contactDetails'])
        ->middleware(['permission:chats.view', 'platform.feature:chat']);
    Route::post('/contacts/{contact}/toggle-bot', [App\Http\Controllers\AdminController::class, 'toggleBot'])
        ->middleware(['permission:chats.toggle_bot', 'platform.feature:chat'])
        ->name('contact.toggle-bot');
    Route::post('/contacts/{contact}/dismiss-agent', [App\Http\Controllers\AdminController::class, 'dismissAgentRequest'])
        ->middleware(['permission:chats.send', 'platform.feature:chat'])
        ->name('contact.dismiss-agent');

    Route::get('/menus', [App\Http\Controllers\Admin\ChatbotController::class, 'menus'])
        ->middleware('permission:menus.view,menus.menu')
        ->name('menus.index');
    Route::post('/menus', [App\Http\Controllers\Admin\ChatbotController::class, 'storeMenu'])
        ->middleware('permission:menus.update')
        ->name('menus.store');
    Route::get('/menus/{menu}', [App\Http\Controllers\Admin\ChatbotController::class, 'showMenu'])
        ->middleware('permission:menus.view')
        ->name('menus.show');
    Route::put('/menus/{menu}', [App\Http\Controllers\Admin\ChatbotController::class, 'updateMenu'])
        ->middleware('permission:menus.update')
        ->name('menus.update');
    Route::delete('/menus/{menu}', [App\Http\Controllers\Admin\ChatbotController::class, 'deleteMenu'])
        ->middleware('permission:menus.update')
        ->name('menus.delete');

    Route::post('/menu-items', [App\Http\Controllers\Admin\ChatbotController::class, 'storeMenuItem'])
        ->middleware('permission:menus.update')
        ->name('menu-items.store');
    Route::get('/menu-items/{item}', [App\Http\Controllers\Admin\ChatbotController::class, 'showMenuItem'])
        ->middleware('permission:menus.view')
        ->name('menu-items.show');
    Route::put('/menu-items/{item}', [App\Http\Controllers\Admin\ChatbotController::class, 'updateMenuItem'])
        ->middleware('permission:menus.update')
        ->name('menu-items.update');
    Route::delete('/menu-items/{item}', [App\Http\Controllers\Admin\ChatbotController::class, 'deleteMenuItem'])
        ->middleware('permission:menus.update')
        ->name('menu-items.delete');

    Route::get('/products', [App\Http\Controllers\Admin\ProductController::class, 'index'])
        ->middleware('permission:products.view,products.menu')
        ->name('products.index');
    Route::get('/products/create', [App\Http\Controllers\Admin\ProductController::class, 'create'])
        ->middleware('permission:products.update')
        ->name('products.create');
    Route::post('/products', [App\Http\Controllers\Admin\ProductController::class, 'store'])
        ->middleware('permission:products.update')
        ->name('products.store');
    Route::get('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'show'])
        ->middleware('permission:products.view')
        ->name('products.show');
    Route::get('/products/{product}/edit', [App\Http\Controllers\Admin\ProductController::class, 'edit'])
        ->middleware('permission:products.update')
        ->name('products.edit');
    Route::put('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'update'])
        ->middleware('permission:products.update')
        ->name('products.update');
    Route::delete('/products/{product}', [App\Http\Controllers\Admin\ProductController::class, 'destroy'])
        ->middleware('permission:products.update')
        ->name('products.destroy');

    Route::get('/chatbot/config', [App\Http\Controllers\Admin\ChatbotController::class, 'config'])
        ->middleware('permission:chatbot.view,chatbot.menu')
        ->name('chatbot.config');
    Route::put('/chatbot/config', [App\Http\Controllers\Admin\ChatbotController::class, 'updateConfig'])
        ->middleware('permission:chatbot.update')
        ->name('chatbot.config.update');

    Route::get('/marketing-flow', [App\Http\Controllers\Admin\MarketingFlowController::class, 'edit'])
        ->middleware('permission:marketing_flow.view,marketing_flow.menu')
        ->name('marketing-flow.edit');
    Route::put('/marketing-flow', [App\Http\Controllers\Admin\MarketingFlowController::class, 'update'])
        ->middleware('permission:marketing_flow.update')
        ->name('marketing-flow.update');

    Route::get('/pricing-settings', [App\Http\Controllers\Admin\PricingSettingsController::class, 'edit'])
        ->middleware('permission:pricing_settings.view,pricing_settings.menu')
        ->name('pricing-settings.edit');
    Route::put('/pricing-settings', [App\Http\Controllers\Admin\PricingSettingsController::class, 'update'])
        ->middleware('permission:pricing_settings.update')
        ->name('pricing-settings.update');
    Route::put('/pricing-settings/billing', [App\Http\Controllers\Admin\PricingSettingsController::class, 'updateBilling'])
        ->middleware('permission:pricing_settings.update')
        ->name('pricing-settings.billing.update');
    Route::post('/platform-receipts/{receipt}/review', [App\Http\Controllers\Admin\PricingSettingsController::class, 'reviewReceipt'])
        ->middleware('permission:pricing_settings.update')
        ->name('platform-receipts.review');

    Route::get('/wallet', [App\Http\Controllers\Admin\WalletController::class, 'index'])
        ->middleware('permission:wallet.view,wallet.menu')
        ->name('wallet.index');
    Route::post('/wallet/receipts', [App\Http\Controllers\Admin\WalletController::class, 'store'])
        ->middleware('permission:wallet.submit')
        ->name('wallet.receipts.store');

    Route::get('/roles', [App\Http\Controllers\Admin\RoleController::class, 'index'])
        ->middleware('permission:roles.view,roles.menu')
        ->name('roles.index');
    Route::post('/roles', [App\Http\Controllers\Admin\RoleController::class, 'store'])
        ->middleware('permission:roles.update')
        ->name('roles.store');
    Route::put('/roles/{role}/permissions', [App\Http\Controllers\Admin\RoleController::class, 'updatePermissions'])
        ->middleware('permission:roles.update')
        ->name('roles.permissions.update');
    Route::delete('/roles/{role}', [App\Http\Controllers\Admin\RoleController::class, 'destroy'])
        ->middleware('permission:roles.update')
        ->name('roles.destroy');

    Route::get('/users', [App\Http\Controllers\Admin\UserAdminController::class, 'index'])
        ->middleware('permission:users.view,users.menu')
        ->name('users.index');
    Route::get('/users/create', [App\Http\Controllers\Admin\UserAdminController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');
    Route::post('/users', [App\Http\Controllers\Admin\UserAdminController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');
    Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserAdminController::class, 'edit'])
        ->middleware('permission:users.update')
        ->name('users.edit');
    Route::put('/users/{user}', [App\Http\Controllers\Admin\UserAdminController::class, 'update'])
        ->middleware('permission:users.update')
        ->name('users.update');
    Route::patch('/users/{user}/toggle-active', [App\Http\Controllers\Admin\UserAdminController::class, 'toggleActive'])
        ->middleware('permission:users.update')
        ->name('users.toggle-active');
    Route::put('/users/{user}/role', [App\Http\Controllers\Admin\UserAdminController::class, 'updateRole'])
        ->middleware('permission:users.update,roles.update')
        ->name('users.role.update');

    Route::post('/demo/reset', [App\Http\Controllers\Admin\DemoResetController::class, 'store'])
        ->middleware('permission:demo.reset')
        ->name('demo.reset');

    Route::get('/profile', [App\Http\Controllers\Admin\UserProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [App\Http\Controllers\Admin\UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\Admin\UserProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::post('/admin/orders/{id}/status', [App\Http\Controllers\AdminController::class, 'updateOrderStatus'])
    ->middleware(['auth', 'admin', 'permission:orders.update', 'platform.feature:orders']);
