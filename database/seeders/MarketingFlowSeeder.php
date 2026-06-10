<?php

namespace Database\Seeders;

use App\Enums\MarketingStepKey;
use App\Models\MarketingFlow;
use App\Models\MarketingFlowStep;
use App\Models\WhatsappBusinessProfile;
use Illuminate\Database\Seeder;

class MarketingFlowSeeder extends Seeder
{
    public function run(): void
    {
        $profile = WhatsappBusinessProfile::first();
        if (!$profile) {
            $this->command?->warn('MarketingFlowSeeder: sin perfil de negocio.');

            return;
        }

        $flow = MarketingFlow::updateOrCreate(
            ['business_profile_id' => $profile->id, 'is_default' => true],
            ['name' => 'Flujo principal de ventas', 'is_active' => true]
        );

        $steps = [
            MarketingStepKey::WELCOME => [
                'message' => "¡Hola {{nombre}}! 👋\n\nBienvenido a *{{nombre_empresa}}*. Soy tu asistente de ventas por WhatsApp.\n\n¿En qué puedo ayudarte hoy?",
                'type' => 'text',
            ],
            MarketingStepKey::MAIN_MENU => [
                'message' => "¿En qué más puedo ayudarte?",
                'type' => 'button',
                'buttons' => [
                    ['id' => 'menu_productos', 'title' => '🛍️ Productos', 'action' => 'products'],
                    ['id' => 'menu_pedido', 'title' => '📦 Pedidos', 'action' => 'orders'],
                    ['id' => 'menu_info', 'title' => 'ℹ️ Información', 'action' => 'info'],
                ],
            ],
            MarketingStepKey::PRODUCTS_MENU => [
                'message' => "🛍️ *Catálogo de productos*\n\nSelecciona un producto para ver más detalles.\nTambién puedes escribir el código SKU directamente.",
                'type' => 'list',
                'catalog_source' => 'products',
                'max_product_rows' => 8,
                'include_navigation' => true,
                'list' => [
                    'button' => 'Ver productos',
                    'sections' => [],
                ],
            ],
            MarketingStepKey::ORDERS_MENU => [
                'message' => "📦 *Tus pedidos*\n\nConsulta el estado de tus compras recientes.",
                'type' => 'text',
            ],
            MarketingStepKey::INFO_MENU => [
                'message' => "ℹ️ *Información*\n\nSelecciona el tema que necesitas:",
                'type' => 'list',
                'list' => [
                    'button' => 'Ver opciones',
                    'sections' => [[
                        'title' => 'Información general',
                        'rows' => [
                            ['id' => 'horarios', 'title' => 'Horarios', 'description' => 'Horario de atención', 'action' => 'horarios'],
                            ['id' => 'contacto', 'title' => 'Contacto', 'description' => 'Datos de contacto', 'action' => 'contacto'],
                            ['id' => 'envios', 'title' => 'Envíos', 'description' => 'Política de envíos', 'action' => 'envios'],
                            ['id' => 'pagos', 'title' => 'Pagos', 'description' => 'Formas de pago', 'action' => 'pagos'],
                        ],
                    ]],
                ],
            ],
            MarketingStepKey::CART_SUMMARY => [
                'message' => "🛒 *Tu carrito*\n\nTotal: {{moneda}} {{total}}\nArtículos: {{cantidad_items}}",
                'type' => 'button',
                'buttons' => [
                    ['id' => 'checkout', 'title' => '💳 Pagar', 'action' => 'checkout'],
                    ['id' => 'menu_productos', 'title' => 'Seguir comprando', 'action' => 'products'],
                    ['id' => 'menu_principal', 'title' => 'Menú principal', 'action' => 'main_menu'],
                ],
            ],
            MarketingStepKey::CHECKOUT => [
                'message' => "💳 *Proceso de pago*\n\nIndícanos tu método de pago preferido o envía tu comprobante cuando hayas pagado.",
                'type' => 'text',
            ],
            MarketingStepKey::PAYMENT_PROOF => [
                'message' => "📎 *Comprobante de pago*\n\nEnvía una foto o PDF de tu comprobante para validar tu pedido.",
                'type' => 'text',
                'success_message' => "✅ Recibimos tu comprobante. Lo validaremos y te confirmaremos por este medio.",
            ],
            MarketingStepKey::AGENT_HANDOFF => [
                'message' => "👤 *Atención humana*\n\nTe conectamos con un asesor de ventas. Por favor espera un momento.",
                'type' => 'text',
            ],
            MarketingStepKey::FALLBACK_MESSAGE => [
                'message' => "No entendí tu mensaje 🤔\n\nPuedes usar el menú o escribir *hola* para comenzar de nuevo.",
                'type' => 'button',
                'buttons' => [
                    ['id' => 'menu_principal', 'title' => 'Menú principal', 'action' => 'main_menu'],
                    ['id' => 'menu_productos', 'title' => 'Ver productos', 'action' => 'products'],
                    ['id' => 'menu_agent', 'title' => 'Hablar con asesor', 'action' => 'agent'],
                ],
            ],
        ];

        $order = 0;
        foreach (MarketingStepKey::defaultOrder() as $stepKey) {
            $order++;
            $data = $steps[$stepKey] ?? ['message' => '', 'type' => 'text'];

            $config = ['interactive_type' => $data['type']];
            if (!empty($data['buttons'])) {
                $config['buttons'] = $data['buttons'];
            }
            if (!empty($data['list'])) {
                $config['list'] = $data['list'];
            }
            if (!empty($data['catalog_source'])) {
                $config['catalog_source'] = $data['catalog_source'];
            }
            if (isset($data['max_product_rows'])) {
                $config['max_product_rows'] = $data['max_product_rows'];
            }
            if (isset($data['include_navigation'])) {
                $config['include_navigation'] = $data['include_navigation'];
            }
            if (!empty($data['success_message'])) {
                $config['success_message'] = $data['success_message'];
            }

            MarketingFlowStep::updateOrCreate(
                ['flow_id' => $flow->id, 'step_key' => $stepKey],
                [
                    'name' => MarketingStepKey::all()[$stepKey],
                    'message_template' => $data['message'],
                    'sort_order' => $order,
                    'is_enabled' => true,
                    'config' => $config,
                ]
            );
        }
    }
}
