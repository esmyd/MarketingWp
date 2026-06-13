<?php

namespace Database\Seeders;

use App\Models\WhatsappBusinessProfile;
use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WhatsappPricesSeeder extends Seeder
{
    public function run(): void
    {
        $profileId = WhatsappBusinessProfile::first()?->id ?? 1;

        $mainMenu = WhatsappMenu::updateOrCreate(
                ['action_id' => 'prices_menu'],
                [
                'business_profile_id' => $profileId,
                'title'       => 'Catálogo de Soluciones',
                'description' => 'Soluciones tecnológicas para tu empresa',
                'type'        => 'list',
                'content'     => "🛍️ *Catálogo de Soluciones Tecnológicas*\n\nSelecciona una categoría para explorar nuestros servicios y precios.\n\nTambién puedes escribir el código SKU directamente (ej: CB-001).",
                    'button_text' => 'Ver soluciones',
                'icon'        => '💻',
                'order'       => 1,
                'is_active'   => true,
            ]
        );

        $categories = [
            ['action_id' => 'software',      'title' => 'Software Empresarial', 'description' => 'CRM, ERP, Facturación, Nómina',     'icon' => '🖥️', 'order' => 1],
            ['action_id' => 'desarrollo_web','title' => 'Desarrollo Web',       'description' => 'Páginas web y aplicaciones',        'icon' => '🌐', 'order' => 2],
            ['action_id' => 'ecommerce',     'title' => 'E-commerce',           'description' => 'Tiendas online y marketplaces',     'icon' => '🛍️', 'order' => 3],
            ['action_id' => 'chatbots',      'title' => 'Chatbots con IA',      'description' => 'Automatización con WhatsApp e IA',  'icon' => '🤖', 'order' => 4],
            ['action_id' => 'automatizacion','title' => 'Automatización',       'description' => 'Procesos y marketing digital',      'icon' => '⚡', 'order' => 5],
            ['action_id' => 'apps_moviles',  'title' => 'Apps Móviles',         'description' => 'Aplicaciones iOS y Android',        'icon' => '📱', 'order' => 6],
        ];

        foreach ($categories as $cat) {
            WhatsappMenuItem::updateOrCreate(
                ['menu_id' => $mainMenu->id, 'action_id' => $cat['action_id']],
                [
                    'title'       => $cat['title'],
                    'description' => $cat['description'],
                    'icon'        => $cat['icon'],
                    'order'       => $cat['order'],
                    'is_active'   => true,
                    ]
                );
            }

            $products = [
            // ─── Software Empresarial ───────────────────────────────────────────
            [
                'sku'           => 'SW-001',
                'name'          => 'CRM Básico',
                'description'   => 'Gestión de clientes y seguimiento de leads para PyMEs. Hasta 5 usuarios.',
                'price'         => 49.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'software',
                'category_name' => 'Software Empresarial',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'usuarios' => '5'],
            ],
            [
                'sku'           => 'SW-002',
                'name'          => 'CRM Empresarial',
                'description'   => 'CRM completo con automatización, reportes avanzados y usuarios ilimitados.',
                'price'         => 149.00,
                'promo_price'   => 129.00,
                'is_promo'      => true,
                'category_id'   => 'software',
                'category_name' => 'Software Empresarial',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'usuarios' => 'Ilimitados'],
            ],
            [
                'sku'           => 'SW-003',
                'name'          => 'Sistema ERP',
                'description'   => 'Gestión integral: inventario, finanzas, RRHH y operaciones en un solo sistema.',
                'price'         => 299.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'software',
                'category_name' => 'Software Empresarial',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual'],
            ],
            [
                'sku'           => 'SW-004',
                'name'          => 'Facturación Electrónica',
                'description'   => 'Sistema de facturación + inventario + reportes fiscales. Cumplimiento total.',
                'price'         => 79.00,
                'promo_price'   => 59.00,
                'is_promo'      => true,
                'category_id'   => 'software',
                'category_name' => 'Software Empresarial',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual'],
            ],
            [
                'sku'           => 'SW-005',
                'name'          => 'Suite Empresarial',
                'description'   => 'CRM + ERP + Facturación + Nómina en un solo paquete. Todo incluido.',
                'price'         => 399.00,
                'promo_price'   => 349.00,
                'is_promo'      => true,
                'category_id'   => 'software',
                'category_name' => 'Software Empresarial',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'ahorro' => '$50/mes'],
            ],

            // ─── Desarrollo Web ────────────────────────────────────────────────
            [
                'sku'           => 'WEB-001',
                'name'          => 'Landing Page',
                'description'   => 'Página de captura profesional con diseño responsivo y formulario de leads.',
                'price'         => 199.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'desarrollo_web',
                'category_name' => 'Desarrollo Web',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'entrega' => '7 días'],
            ],
            [
                'sku'           => 'WEB-002',
                'name'          => 'Página Corporativa',
                'description'   => 'Sitio web de 5-10 páginas, SEO básico, dominio y hosting incluido 1 año.',
                'price'         => 499.00,
                'promo_price'   => 449.00,
                'is_promo'      => true,
                'category_id'   => 'desarrollo_web',
                'category_name' => 'Desarrollo Web',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'entrega' => '15 días'],
            ],
            [
                'sku'           => 'WEB-003',
                'name'          => 'Aplicación Web',
                'description'   => 'Desarrollo de aplicación web personalizada a medida. Backend + Frontend.',
                'price'         => 1999.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'desarrollo_web',
                'category_name' => 'Desarrollo Web',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'stack' => 'Laravel + Vue.js'],
            ],

            // ─── E-commerce ────────────────────────────────────────────────────
            [
                'sku'           => 'EC-001',
                'name'          => 'Tienda Online Básica',
                'description'   => 'Catálogo de productos, carrito y pasarela de pagos integrada.',
                'price'         => 599.00,
                'promo_price'   => 549.00,
                'is_promo'      => true,
                'category_id'   => 'ecommerce',
                'category_name' => 'E-commerce',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'entrega' => '20 días'],
            ],
            [
                'sku'           => 'EC-002',
                'name'          => 'Tienda Multi-vendedor',
                'description'   => 'Marketplace con panel de vendedores, comisiones y múltiples pagos.',
                'price'         => 1299.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'ecommerce',
                'category_name' => 'E-commerce',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'entrega' => '30 días'],
            ],

            // ─── Chatbots con IA ───────────────────────────────────────────────
            [
                'sku'           => 'CB-001',
                'name'          => 'Chatbot Básico',
                'description'   => 'Menú WhatsApp + respuestas automáticas. 1 número, hasta 1000 msgs/mes.',
                'price'         => 30.00,
                'promo_price'   => 25.00,
                'is_promo'      => true,
                'category_id'   => 'chatbots',
                'category_name' => 'Chatbots con IA',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'mensajes' => '1.000/mes'],
            ],
            [
                'sku'           => 'CB-002',
                'name'          => 'Chatbot Profesional',
                'description'   => 'Chatbot + catálogo + carrito de compras + CRM. Hasta 3 números WA.',
                'price'         => 80.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'chatbots',
                'category_name' => 'Chatbots con IA',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'mensajes' => '5.000/mes'],
            ],
            [
                'sku'           => 'CB-003',
                'name'          => 'Chatbot Empresarial',
                'description'   => 'IA conversacional, mensajes ilimitados, números ilimitados. Soporte 24/7.',
                'price'         => 200.00,
                'promo_price'   => 150.00,
                'is_promo'      => true,
                'category_id'   => 'chatbots',
                'category_name' => 'Chatbots con IA',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'mensajes' => 'Ilimitados'],
            ],
            [
                'sku'           => 'CB-004',
                'name'          => 'Chatbot con GPT',
                'description'   => 'Integración con ChatGPT para respuestas inteligentes y naturales.',
                'price'         => 300.00,
                'promo_price'   => 249.00,
                'is_promo'      => true,
                'category_id'   => 'chatbots',
                'category_name' => 'Chatbots con IA',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'ia' => 'GPT-4'],
            ],

            // ─── Automatización ────────────────────────────────────────────────
            [
                'sku'           => 'AUTO-001',
                'name'          => 'Marketing Automation',
                'description'   => 'Email marketing + redes sociales + segmentación y analytics en tiempo real.',
                'price'         => 499.00,
                'promo_price'   => 449.00,
                'is_promo'      => true,
                'category_id'   => 'automatizacion',
                'category_name' => 'Automatización',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual'],
            ],
            [
                'sku'           => 'AUTO-002',
                'name'          => 'Automatización WA',
                'description'   => 'Campañas masivas WhatsApp + seguimiento + reportes de entrega y lectura.',
                'price'         => 199.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'automatizacion',
                'category_name' => 'Automatización',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual'],
            ],
            [
                'sku'           => 'AUTO-003',
                'name'          => 'Automatización Integral',
                'description'   => 'Marketing + WhatsApp + procesos internos. Solución 360° para tu negocio.',
                'price'         => 599.00,
                'promo_price'   => 499.00,
                'is_promo'      => true,
                'category_id'   => 'automatizacion',
                'category_name' => 'Automatización',
                'metadata'      => ['tipo' => 'SaaS', 'facturacion' => 'mensual', 'ahorro' => '20%'],
            ],

            // ─── Apps Móviles ──────────────────────────────────────────────────
            [
                'sku'           => 'APP-001',
                'name'          => 'App Híbrida',
                'description'   => 'App iOS + Android con panel de administración. Flutter + Laravel.',
                'price'         => 2999.00,
                'promo_price'   => 2499.00,
                'is_promo'      => true,
                'category_id'   => 'apps_moviles',
                'category_name' => 'Apps Móviles',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'stack' => 'Flutter + Laravel'],
            ],
            [
                'sku'           => 'APP-002',
                'name'          => 'Sistema de Reservas',
                'description'   => 'App de citas y reservas online con pagos, recordatorios y estadísticas.',
                'price'         => 799.00,
                'promo_price'   => null,
                'is_promo'      => false,
                'category_id'   => 'apps_moviles',
                'category_name' => 'Apps Móviles',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único'],
            ],
            [
                'sku'           => 'APP-003',
                'name'          => 'App de Delivery',
                'description'   => 'App completa de pedidos y delivery con tracking en tiempo real.',
                'price'         => 2499.00,
                'promo_price'   => 1999.00,
                'is_promo'      => true,
                'category_id'   => 'apps_moviles',
                'category_name' => 'Apps Móviles',
                'metadata'      => ['tipo' => 'Servicio', 'facturacion' => 'pago único', 'entrega' => '45 días'],
            ],
            ];

            foreach ($products as $product) {
            $menuItem = WhatsappMenuItem::where('menu_id', $mainMenu->id)
                ->where('action_id', $product['category_id'])
                ->first();

            if (!$menuItem) {
                continue;
            }

            WhatsappPrice::updateOrCreate(
                            ['sku' => $product['sku']],
                            [
                    'menu_item_id'            => $menuItem->id,
                    'category'                => $product['category_name'],
                    'name'                    => Str::limit($product['name'], 24, ''),
                    'description'             => $product['description'],
                    'price'                   => $product['price'],
                    'promo_price'             => $product['promo_price'],
                    'is_promo'                => $product['is_promo'],
                    'promo_start_date'        => $product['is_promo'] ? now() : null,
                    'promo_end_date'          => $product['is_promo'] ? now()->addDays(60) : null,
                    'currency'                => 'USD',
                    'is_active'               => true,
                    'stock'                   => 999,
                    'allow_quantity_selection' => false,
                    'min_quantity'            => 1,
                    'max_quantity'            => 1,
                    'characteristics'         => [],
                    'metadata'                => $product['metadata'],
                ]
            );
        }
    }
}
