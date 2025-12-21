<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsappWebhookController;
use App\Http\Controllers\HerbalifeMarketingController;
use App\Http\Controllers\WhatsappTemplateController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// WhatsApp Webhook Routes
Route::prefix('whatsapp')->group(function () {
    // Ruta para la verificación del webhook
    Route::get('webhook', [WhatsappWebhookController::class, 'verify']);

    // Ruta para recibir las actualizaciones
    Route::post('webhook', [WhatsappWebhookController::class, 'webhook']);

    // Ruta para obtener las plantillas aprobadas
    Route::get('/templates/approved', [WhatsappTemplateController::class, 'getApprovedTemplates']);
});

Route::post('/herbalife/marketing', [HerbalifeMarketingController::class, 'sendMarketingMessage']);
Route::post('/herbalife/verify-template', [HerbalifeMarketingController::class, 'verifyAndSubmitTemplate']);
