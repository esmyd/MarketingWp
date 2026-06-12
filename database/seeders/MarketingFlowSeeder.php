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
                'message' => "¡Hola {{nombre}}! 👋\n\nBienvenido a *{{nombre_empresa}}*. 💻\n\nSoy *{{nombre_bot}}*, tu asistente virtual de ventas y estoy aquí para ayudarte a encontrar la solución tecnológica perfecta para tu negocio.\n\n¿En qué puedo ayudarte hoy?",
                'type'    => 'text',
            ],

            MarketingStepKey::MAIN_MENU => [
                'message' => "💼 *¿Cómo puedo ayudarte?*\n\nSelecciona una opción para continuar:",
                'type'    => 'button',
                'buttons' => [
                    ['id' => 'menu_productos', 'title' => '💻 Soluciones',  'action' => 'products'],
                    ['id' => 'menu_pedido',    'title' => '📦 Mis Pedidos', 'action' => 'orders'],
                    ['id' => 'menu_info',      'title' => 'ℹ️ Información', 'action' => 'info'],
                ],
            ],

            MarketingStepKey::PRODUCTS_MENU => [
                'message'           => "💻 *Catálogo de Soluciones Tecnológicas*\n\n*{{nombre_empresa}}* ofrece *{{total_productos}} servicios* en *{{total_categorias}} categorías*.\n\nSelecciona un servicio para conocer detalles y precios:",
                'type'              => 'list',
                'catalog_source'    => 'categories',
                'max_product_rows'  => 8,
                'include_navigation'=> true,
                'list'              => ['button' => '🔍 Ver soluciones', 'sections' => []],
            ],

            MarketingStepKey::ORDERS_MENU => [
                'message' => "📦 *Estado de tus Proyectos*\n\nConsulta el avance y estado de tus proyectos contratados con *{{nombre_empresa}}*.\n\nSi necesitas asistencia inmediata, escríbenos al {{telefono_soporte}}.",
                'type'    => 'text',
            ],

            MarketingStepKey::INFO_MENU => [
                'message' => "ℹ️ *Información — {{nombre_empresa}}*\n\nSelecciona el tema que necesitas:",
                'type'    => 'list',
                'list'    => [
                    'button'   => 'Ver información',
                    'sections' => [
                        [
                            'title' => 'Atención al Cliente',
                            'rows'  => [
                                ['id' => 'horarios', 'title' => '🕒 Horarios',     'description' => 'Horarios de atención', 'action' => 'horarios'],
                                ['id' => 'contacto', 'title' => '📞 Contacto',     'description' => 'Teléfono y email',    'action' => 'contacto'],
                                ['id' => 'asesoria', 'title' => '👨‍💼 Asesoría',    'description' => 'Consulta gratuita',   'action' => 'asesoria'],
                            ],
                        ],
                        [
                            'title' => 'Servicios',
                            'rows'  => [
                                ['id' => 'pagos',  'title' => '💳 Métodos de Pago', 'description' => 'Formas de pago',         'action' => 'pagos'],
                                ['id' => 'envios', 'title' => '🚀 Entregas',        'description' => 'Tiempos de entrega',     'action' => 'envios'],
                                ['id' => 'redes',  'title' => '📱 Redes Sociales',  'description' => 'Síguenos en redes',      'action' => 'redes'],
                            ],
                        ],
                    ],
                ],
            ],

            MarketingStepKey::CART_SUMMARY => [
                'message' => "🛒 *Resumen de tu Pedido*\n\n📦 Servicios seleccionados: *{{cantidad_items}}*\n💰 Total: *{{moneda}} {{total}}*\n\n¿Cómo deseas continuar?",
                'type'    => 'button',
                'buttons' => [
                    ['id' => 'checkout',       'title' => '💳 Contratar',      'action' => 'checkout'],
                    ['id' => 'menu_productos',  'title' => '➕ Agregar más',    'action' => 'products'],
                    ['id' => 'menu_principal',  'title' => '🏠 Menú principal', 'action' => 'main_menu'],
                ],
            ],

            MarketingStepKey::CHECKOUT => [
                'message' => "💳 *Proceso de Contratación*\n\nPara formalizar tu contratación con *{{nombre_empresa}}*, puedes:\n\n1️⃣ Pagar por transferencia bancaria\n2️⃣ Pagar con tarjeta de crédito/débito\n3️⃣ Pago en efectivo en oficina\n\nUna vez confirmado el pago, nuestro equipo se contactará contigo en *máximo 24 horas hábiles*.\n\nPara más información: {{telefono_soporte}}",
                'type'    => 'text',
            ],

            MarketingStepKey::PAYMENT_PROOF => [
                'message'         => "📎 *Envío de Comprobante*\n\nPedido *{{numero_pedido}}* — Total: *{{moneda}} {{total}}*\nMétodo: *{{metodo_pago}}*\n\nEnvía una imagen o PDF de tu comprobante de pago para validar tu contratación.\n\nAsegúrate de incluir:\n✅ Monto pagado\n✅ Fecha de la transacción\n✅ Nombre del titular",
                'type'            => 'text',
                'require_proof'   => true,
                'require_for_methods' => ['transferencia', 'tarjeta'],
                'success_message' => "✅ *¡Comprobante recibido!*\n\nHemos recibido tu comprobante de pago del pedido *{{numero_pedido}}*. Nuestro equipo lo verificará y te confirmará por este medio en un plazo máximo de *2 horas hábiles*.\n\n¡Gracias por confiar en *{{nombre_empresa}}*! 🚀",
            ],

            MarketingStepKey::AGENT_HANDOFF => [
                'message' => "👨‍💼 *Conectando con un Asesor*\n\nEstamos transfiriendo tu conversación a uno de nuestros especialistas tecnológicos.\n\n⏱️ Tiempo de respuesta estimado: *5-10 minutos*\n📅 Horario de atención: Lunes a Viernes 9:00 - 18:00\n\nTambién puedes contactarnos directamente al *{{telefono_soporte}}*.\n\n¡Gracias por tu paciencia!",
                'type'    => 'text',
            ],

            MarketingStepKey::FALLBACK_MESSAGE => [
                'message' => "🤔 *No reconocí tu mensaje*\n\nPuedes usar el menú de opciones o escribir palabras clave como:\n• *hola* — para ver el menú principal\n• *productos* — ver el catálogo\n• *precios* — consultar tarifas\n• *contacto* — hablar con un asesor\n\n¿En qué puedo ayudarte?",
                'type'    => 'button',
                'buttons' => [
                    ['id' => 'menu_principal', 'title' => '🏠 Menú principal', 'action' => 'main_menu'],
                    ['id' => 'menu_productos', 'title' => '💻 Ver soluciones', 'action' => 'products'],
                    ['id' => 'menu_agent',     'title' => '👨‍💼 Hablar con asesor','action' => 'agent'],
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
                $config['catalog_source']     = $data['catalog_source'];
                $config['max_product_rows']   = $data['max_product_rows'] ?? 8;
                $config['include_navigation'] = $data['include_navigation'] ?? true;
            }

            if (!empty($data['success_message'])) {
                $config['success_message'] = $data['success_message'];
            }

            if (!empty($data['require_proof'])) {
                $config['require_proof'] = true;
                $config['require_for_methods'] = $data['require_for_methods'] ?? ['transferencia', 'tarjeta'];
            }

            MarketingFlowStep::updateOrCreate(
                ['flow_id' => $flow->id, 'step_key' => $stepKey],
                [
                    'name'             => MarketingStepKey::all()[$stepKey],
                    'message_template' => $data['message'],
                    'sort_order'       => $order,
                    'is_enabled'       => true,
                    'config'           => $config,
                ]
            );
        }
    }
}
