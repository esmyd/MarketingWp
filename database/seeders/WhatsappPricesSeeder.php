<?php

namespace Database\Seeders;

use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappPricesSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Get or create the main menu
            $mainMenu = WhatsappMenu::firstOrCreate(
                ['action_id' => 'prices_menu'],
                [
                    'business_profile_id' => 1,
                    'title' => 'Soluciones Tecnológicas',
                    'description' => 'Selecciona una categoría de soluciones',
                    'type' => 'list',
                    'content' => 'Nuestras soluciones tecnológicas',
                    'button_text' => 'Ver soluciones',
                    'icon' => '💻',
                    'order' => 1,
                    'is_active' => true
                ]
            );

            // Crear categorías principales del menú
            $menuCategories = [
                [
                    'action_id' => 'software',
                    'title' => 'Software Empresarial',
                    'description' => 'Soluciones de software para empresas',
                    'icon' => '🖥️',
                    'order' => 1
                ],
                [
                    'action_id' => 'web_design',
                    'title' => 'Desarrollo Web',
                    'description' => 'Diseño y desarrollo de sitios web',
                    'icon' => '🌐',
                    'order' => 2
                ],
                [
                    'action_id' => 'ecommerce',
                    'title' => 'E-commerce',
                    'description' => 'Soluciones de comercio electrónico',
                    'icon' => '🛍️',
                    'order' => 3
                ],
                [
                    'action_id' => 'chatbots',
                    'title' => 'Chatbots',
                    'description' => 'Soluciones de chatbot para WhatsApp',
                    'icon' => '🤖',
                    'order' => 4
                ],
                [
                    'action_id' => 'automation',
                    'title' => 'Automatización',
                    'description' => 'Soluciones de automatización empresarial',
                    'icon' => '⚡',
                    'order' => 5
                ]
            ];

            // Crear los items del menú
            foreach ($menuCategories as $category) {
                WhatsappMenuItem::firstOrCreate(
                    [
                        'menu_id' => $mainMenu->id,
                        'action_id' => $category['action_id']
                    ],
                    [
                        'title' => $category['title'],
                        'description' => $category['description'],
                        'icon' => $category['icon'],
                        'order' => $category['order'],
                        'is_active' => true
                    ]
                );
            }

            $products = [
                [
                    'sku' => '1001',
                    'name' => 'Batido F1 Vainilla',
                    'description' => 'Batido nutricional con proteína de soya y 23 vitaminas y minerales esenciales',
                    'price' => 899.00,
                    'category' => 'Control de Peso',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Proteína de soya de alta calidad',
                        '23 vitaminas y minerales',
                        'Bajo en calorías',
                        'Sin azúcares añadidos',
                        'Rico en fibra'
                    ],
                    'metadata' => [
                        'quantity' => '550g',
                        'flavor' => 'Vainilla',
                        'format' => 'Polvo',
                        'benefits' => 'Control de peso, Energía, Nutrición balanceada',
                        'nutritional_info' => 'Proteína: 18g, Carbohidratos: 24g, Grasas: 3g, Calorías: 200'
                    ]
                ],
                [
                    'sku' => '1002',
                    'name' => 'Té Concentrado',
                    'description' => 'Bebida concentrada de té verde y hierbas naturales',
                    'price' => 699.00,
                    'category' => 'Control de Peso',
                    'is_promo' => true,
                    'promo_price' => 599.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/Herbalifeline-1.jpg',
                    'characteristics' => [
                        'Té verde premium',
                        'Hierbas naturales',
                        'Antioxidantes',
                        'Sin conservantes',
                        'Bajo en calorías'
                    ],
                    'metadata' => [
                        'quantity' => '500ml',
                        'flavor' => 'Original',
                        'format' => 'Líquido',
                        'benefits' => 'Metabolismo, Energía, Antioxidantes',
                        'nutritional_info' => 'Calorías: 5 por porción'
                    ]
                ],
                [
                    'sku' => '2001',
                    'name' => 'Aloe Vera',
                    'description' => 'Bebida concentrada de aloe vera para la digestión',
                    'price' => 499.00,
                    'category' => 'Bienestar Digestivo',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/Herbalifeline-1.jpg',
                    'characteristics' => [
                        'Aloe vera puro',
                        'Mejora la digestión',
                        'Hidratación natural',
                        'Sin azúcares añadidos',
                        'Rico en vitaminas'
                    ],
                    'metadata' => [
                        'quantity' => '1L',
                        'flavor' => 'Mango',
                        'format' => 'Líquido',
                        'benefits' => 'Digestión, Hidratación, Bienestar general',
                        'nutritional_info' => 'Calorías: 10 por porción'
                    ]
                ],
                [
                    'sku' => '3001',
                    'name' => 'Multivitamínico',
                    'description' => 'Complejo multivitamínico completo con minerales',
                    'price' => 799.00,
                    'category' => 'Vitaminas',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/Herbalifeline-1.jpg',
                    'characteristics' => [
                        'Vitaminas A-Z',
                        'Minerales esenciales',
                        'Fórmula completa',
                        'Fácil absorción',
                        'Sin gluten'
                    ],
                    'metadata' => [
                        'quantity' => '60 tabletas',
                        'flavor' => 'Natural',
                        'format' => 'Tabletas',
                        'benefits' => 'Nutrición celular, Energía, Sistema inmunológico',
                        'nutritional_info' => 'Vitaminas A-Z, Minerales esenciales'
                    ]
                ],
                [
                    'sku' => '4001',
                    'name' => 'Proteína en Polvo',
                    'description' => 'Proteína de suero de leche de alta calidad',
                    'price' => 1299.00,
                    'category' => 'Proteínas',
                    'is_promo' => true,
                    'promo_price' => 1099.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/proteina.jpg',
                    'characteristics' => [
                        'Proteína de suero premium',
                        'Alta biodisponibilidad',
                        'Bajo en lactosa',
                        'Rico en BCAA',
                        'Fácil de mezclar'
                    ],
                    'metadata' => [
                        'quantity' => '750g',
                        'flavor' => 'Chocolate',
                        'format' => 'Polvo',
                        'benefits' => 'Masa muscular, Recuperación, Nutrición deportiva',
                        'nutritional_info' => 'Proteína: 20g, Carbohidratos: 3g, Grasas: 1g, Calorías: 110'
                    ]
                ],
                [
                    'sku' => '5001',
                    'name' => 'Combo Desayuno Premium',
                    'description' => 'Combo completo para un desayuno saludable y nutritivo',
                    'price' => 2499.00,
                    'category' => 'Combos',
                    'is_promo' => true,
                    'promo_price' => 1999.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Nutrición completa',
                        'Balance perfecto',
                        'Energía duradera',
                        'Control de peso',
                        'Fácil preparación'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Batido F1, Té Concentrado, Aloe Vera',
                        'format' => 'Combo',
                        'benefits' => 'Nutrición completa, Energía, Control de peso',
                        'nutritional_info' => 'Incluye todos los nutrientes necesarios para el día'
                    ]
                ],
                [
                    'sku' => '5002',
                    'name' => 'Combo Bienestar Total',
                    'description' => 'Combo completo para el bienestar general',
                    'price' => 3299.00,
                    'category' => 'Combos',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Nutrición integral',
                        'Bienestar general',
                        'Sistema inmunológico',
                        'Energía natural',
                        'Digestión saludable'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Multivitamínico, Aloe Vera, Proteína en Polvo',
                        'format' => 'Combo',
                        'benefits' => 'Nutrición completa, Digestión, Masa muscular',
                        'nutritional_info' => 'Combinación perfecta para el bienestar general'
                    ]
                ],
                [
                    'sku' => '5003',
                    'name' => 'Combo Desayuno Express',
                    'description' => 'Combo rápido y nutritivo para el desayuno',
                    'price' => 1799.00,
                    'category' => 'Combos',
                    'is_promo' => true,
                    'promo_price' => 1499.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Preparación rápida',
                        'Nutrición esencial',
                        'Energía inmediata',
                        'Control de peso',
                        'Fácil de llevar'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Batido F1, Té Concentrado',
                        'format' => 'Combo',
                        'benefits' => 'Energía rápida, Control de peso',
                        'nutritional_info' => 'Nutrición esencial para empezar el día'
                    ]
                ],
                [
                    'sku' => '6001',
                    'name' => 'Kit Detox 7 Días',
                    'description' => 'Kit completo para programa detox de 7 días',
                    'price' => 3999.00,
                    'category' => 'Kits Especiales',
                    'is_promo' => true,
                    'promo_price' => 3499.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Programa completo',
                        'Desintoxicación natural',
                        'Control de peso',
                        'Energía renovada',
                        'Guía paso a paso'
                    ],
                    'metadata' => [
                        'quantity' => '1 kit',
                        'contents' => 'Té Concentrado, Aloe Vera, Batido F1',
                        'format' => 'Kit',
                        'benefits' => 'Desintoxicación, Control de peso, Energía',
                        'nutritional_info' => 'Programa completo de 7 días'
                    ]
                ],
                [
                    'sku' => '6002',
                    'name' => 'Kit Nutrición Familiar',
                    'description' => 'Kit completo para toda la familia',
                    'price' => 4999.00,
                    'category' => 'Kits Especiales',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Nutrición familiar',
                        'Bienestar general',
                        'Vitaminas esenciales',
                        'Proteínas de calidad',
                        'Hidratación natural'
                    ],
                    'metadata' => [
                        'quantity' => '1 kit',
                        'contents' => 'Multivitamínico x2, Batido F1 x2, Aloe Vera',
                        'format' => 'Kit',
                        'benefits' => 'Nutrición familiar, Bienestar general',
                        'nutritional_info' => 'Nutrición completa para toda la familia'
                    ]
                ],
                [
                    'sku' => '7001',
                    'name' => 'Combo Desayuno Energético',
                    'description' => 'Combo perfecto para empezar el día con energía',
                    'price' => 1299.00,
                    'category' => 'Combos Desayuno',
                    'is_promo' => true,
                    'promo_price' => 1099.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Energía duradera',
                        'Nutrición balanceada',
                        'Proteínas de calidad',
                        'Antioxidantes',
                        'Fácil preparación'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Batido F1 Vainilla, Té Concentrado, Aloe Vera',
                        'format' => 'Combo',
                        'benefits' => 'Energía, Nutrición, Hidratación',
                        'nutritional_info' => 'Proteínas, Antioxidantes, Vitaminas'
                    ]
                ],
                [
                    'sku' => '7002',
                    'name' => 'Combo Desayuno Clásico',
                    'description' => 'Desayuno completo con waffles y huevos',
                    'price' => 1499.00,
                    'category' => 'Combos Desayuno',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Alto en proteínas',
                        'Bajo en carbohidratos',
                        'Energía natural',
                        'Saciedad prolongada',
                        'Fácil de preparar'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Waffles de proteína, Huevos revueltos, Té Concentrado',
                        'format' => 'Combo',
                        'benefits' => 'Proteínas, Energía, Saciedad',
                        'nutritional_info' => 'Alto en proteínas, Bajo en carbohidratos'
                    ]
                ],
                [
                    'sku' => '7003',
                    'name' => 'Combo Merienda Saludable',
                    'description' => 'Merienda nutritiva para media mañana',
                    'price' => 999.00,
                    'category' => 'Combos Desayuno',
                    'is_promo' => true,
                    'promo_price' => 899.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Nutrición esencial',
                        'Energía rápida',
                        'Proteínas de calidad',
                        'Hidratación natural',
                        'Fácil de llevar'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Batido F1 Chocolate, Aloe Vera',
                        'format' => 'Combo',
                        'benefits' => 'Energía, Nutrición, Hidratación',
                        'nutritional_info' => 'Proteínas y vitaminas esenciales'
                    ]
                ],
                [
                    'sku' => '7004',
                    'name' => 'Combo Desayuno Premium',
                    'description' => 'Desayuno gourmet con opciones premium',
                    'price' => 1799.00,
                    'category' => 'Combos Desayuno',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Ingredientes premium',
                        'Nutrición gourmet',
                        'Energía duradera',
                        'Balance perfecto',
                        'Experiencia única'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Waffles de proteína, Huevos benedictinos, Té Concentrado, Aloe Vera',
                        'format' => 'Combo',
                        'benefits' => 'Nutrición premium, Energía duradera',
                        'nutritional_info' => 'Balance perfecto de macronutrientes'
                    ]
                ],
                [
                    'sku' => '7005',
                    'name' => 'Combo Merienda Express',
                    'description' => 'Merienda rápida y nutritiva',
                    'price' => 799.00,
                    'category' => 'Combos Desayuno',
                    'is_promo' => true,
                    'promo_price' => 699.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/formula1_dulceleche.jpg',
                    'characteristics' => [
                        'Preparación rápida',
                        'Nutrición esencial',
                        'Energía inmediata',
                        'Fácil de llevar',
                        'Vitaminas y minerales'
                    ],
                    'metadata' => [
                        'quantity' => '1 combo',
                        'contents' => 'Batido F1 Frutas, Té Concentrado',
                        'format' => 'Combo',
                        'benefits' => 'Energía rápida, Nutrición esencial',
                        'nutritional_info' => 'Vitaminas y minerales esenciales'
                    ]
                ],
                [
                    'sku' => '8001',
                    'name' => 'Plan Básico CRM',
                    'description' => 'Sistema CRM básico para pequeñas empresas',
                    'price' => 49.99,
                    'category' => 'Software',
                    'is_promo' => true,
                    'promo_price' => 39.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Gestión de contactos',
                        'Seguimiento de leads',
                        'Reportes básicos',
                        'Soporte por email',
                        'Hasta 5 usuarios'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Gestión de clientes, Automatización básica',
                        'technical_info' => 'Navegador web, API disponible'
                    ]
                ],
                [
                    'sku' => '8002',
                    'name' => 'Plan Empresarial CRM',
                    'description' => 'Sistema CRM completo para empresas medianas',
                    'price' => 149.99,
                    'category' => 'Software',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Todas las funciones del plan básico',
                        'Automatización avanzada',
                        'Integración con ERP',
                        'Soporte 24/7',
                        'Usuarios ilimitados'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Gestión empresarial, Automatización completa',
                        'technical_info' => 'API REST, Webhooks, SSO'
                    ]
                ],
                [
                    'sku' => '8003',
                    'name' => 'Sistema de Facturación',
                    'description' => 'Software de facturación electrónica y gestión',
                    'price' => 79.99,
                    'category' => 'Software',
                    'is_promo' => true,
                    'promo_price' => 59.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Facturación electrónica',
                        'Gestión de inventario',
                        'Reportes fiscales',
                        'Integración con bancos',
                        'Multi-moneda'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Automatización fiscal, Gestión financiera',
                        'technical_info' => 'API disponible, Certificación SAT'
                    ]
                ],
                [
                    'sku' => '8004',
                    'name' => 'Software de Nómina',
                    'description' => 'Sistema completo de gestión de nómina y RH',
                    'price' => 99.99,
                    'category' => 'Software',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Cálculo automático de nómina',
                        'Gestión de prestaciones',
                        'Reportes IMSS',
                        'Control de asistencia',
                        'Portal de empleados'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Automatización de nómina, Cumplimiento legal',
                        'technical_info' => 'API disponible, Integración con bancos'
                    ]
                ],
                [
                    'sku' => '8005',
                    'name' => 'Suite Empresarial',
                    'description' => 'Paquete completo de software empresarial',
                    'price' => 299.99,
                    'category' => 'Software',
                    'is_promo' => true,
                    'promo_price' => 249.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'CRM completo',
                        'Sistema de facturación',
                        'Gestión de nómina',
                        'ERP básico',
                        'Soporte premium'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Gestión integral, Automatización completa',
                        'technical_info' => 'API completa, Integración total'
                    ]
                ],
                [
                    'sku' => '8006',
                    'name' => 'Software de E-commerce',
                    'description' => 'Plataforma completa de comercio electrónico',
                    'price' => 129.99,
                    'category' => 'Software',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Tienda online personalizable',
                        'Gestión de inventario',
                        'Pasarelas de pago',
                        'Marketing automation',
                        'Analytics avanzado'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Ventas online, Automatización de marketing',
                        'technical_info' => 'API REST, Webhooks, CDN incluido'
                    ]
                ],
                [
                    'sku' => '8007',
                    'name' => 'Software de Proyectos',
                    'description' => 'Gestión completa de proyectos y tareas',
                    'price' => 69.99,
                    'category' => 'Software',
                    'is_promo' => true,
                    'promo_price' => 49.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Gestión de tareas',
                        'Seguimiento de tiempo',
                        'Colaboración en equipo',
                        'Reportes de progreso',
                        'Integración con herramientas'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Productividad, Colaboración, Control',
                        'technical_info' => 'API disponible, Integración con GitHub'
                    ]
                ],
                [
                    'sku' => '9001',
                    'name' => 'Chatbot Básico',
                    'description' => 'Solución básica de chatbot para WhatsApp',
                    'price' => 30.00,
                    'category' => 'Chatbots',
                    'is_promo' => true,
                    'promo_price' => 25.00,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/promocionar.png',
                    'characteristics' => [
                        'Respuestas automáticas',
                        'Menú de productos',
                        'Soporte básico',
                        'Hasta 1000 mensajes/mes',
                        '1 número de WhatsApp'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Atención 24/7, Automatización básica',
                        'technical_info' => 'API de WhatsApp, Panel de control'
                    ]
                ],
                [
                    'sku' => '9002',
                    'name' => 'Chatbot Profesional',
                    'description' => 'Solución profesional de chatbot para WhatsApp',
                    'price' => 79.99,
                    'category' => 'Chatbots',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/promocionar.png',
                    'characteristics' => [
                        'Todas las funciones del plan básico',
                        'Integración con CRM',
                        'Analytics avanzado',
                        'Hasta 5000 mensajes/mes',
                        '3 números de WhatsApp'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Automatización avanzada, Analytics',
                        'technical_info' => 'API completa, Integración con CRM'
                    ]
                ],
                [
                    'sku' => '9003',
                    'name' => 'Chatbot Empresarial',
                    'description' => 'Solución empresarial completa de chatbot',
                    'price' => 199.99,
                    'category' => 'Chatbots',
                    'is_promo' => true,
                    'promo_price' => 149.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/promocionar.png',
                    'characteristics' => [
                        'Todas las funciones del plan profesional',
                        'IA y Machine Learning',
                        'Mensajes ilimitados',
                        'Números ilimitados',
                        'Soporte prioritario 24/7'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Automatización total, IA avanzada',
                        'technical_info' => 'API completa, IA integrada'
                    ]
                ],
                [
                    'sku' => '9004',
                    'name' => 'Página Web Básica',
                    'description' => 'Sitio web básico para pequeñas empresas',
                    'price' => 299.99,
                    'category' => 'Diseño Web',
                    'is_promo' => true,
                    'promo_price' => 249.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Diseño responsivo',
                        'Hasta 5 páginas',
                        'Formulario de contacto',
                        'SEO básico',
                        'Dominio y hosting 1 año'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Presencia online, Diseño profesional',
                        'technical_info' => 'WordPress, Diseño responsivo'
                    ]
                ],
                [
                    'sku' => '9005',
                    'name' => 'Página Web E-commerce',
                    'description' => 'Tienda online completa',
                    'price' => 599.99,
                    'category' => 'E-commerce',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Todas las funciones del plan básico',
                        'Catálogo de productos',
                        'Pasarela de pagos',
                        'Gestión de inventario',
                        'Panel de administración'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Ventas online, Gestión de productos',
                        'technical_info' => 'WooCommerce, SSL, API de pagos'
                    ]
                ],
                [
                    'sku' => '9006',
                    'name' => 'Automatización Empresarial',
                    'description' => 'Solución completa de automatización',
                    'price' => 399.99,
                    'category' => 'Automatización',
                    'is_promo' => true,
                    'promo_price' => 349.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Automatización de procesos',
                        'Integración con WhatsApp',
                        'Gestión de clientes',
                        'Reportes automáticos',
                        'Soporte técnico'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Eficiencia, Automatización total',
                        'technical_info' => 'API completa, Webhooks'
                    ]
                ],
                [
                    'sku' => '9007',
                    'name' => 'Página Web Corporativa',
                    'description' => 'Sitio web profesional para empresas',
                    'price' => 499.99,
                    'category' => 'Diseño Web',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Diseño personalizado',
                        'Hasta 10 páginas',
                        'Blog integrado',
                        'SEO avanzado',
                        'Dominio y hosting 1 año',
                        'Panel de administración'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Imagen corporativa, Marketing digital',
                        'technical_info' => 'WordPress, Diseño personalizado'
                    ]
                ],
                [
                    'sku' => '9008',
                    'name' => 'Aplicación Web Personalizada',
                    'description' => 'Desarrollo de aplicación web a medida',
                    'price' => 1999.99,
                    'category' => 'Diseño Web',
                    'is_promo' => true,
                    'promo_price' => 1799.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Desarrollo a medida',
                        'Backend personalizado',
                        'Frontend moderno',
                        'Base de datos optimizada',
                        'API REST',
                        'Documentación completa'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Solución personalizada, Escalabilidad',
                        'technical_info' => 'Laravel, Vue.js, MySQL'
                    ]
                ],
                [
                    'sku' => '9009',
                    'name' => 'Tienda Multi-vendedor',
                    'description' => 'Plataforma de marketplace completa',
                    'price' => 899.99,
                    'category' => 'E-commerce',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Panel de vendedores',
                        'Comisiones automáticas',
                        'Múltiples pasarelas de pago',
                        'Gestión de envíos',
                        'Sistema de calificaciones',
                        'Reportes avanzados'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Marketplace, Múltiples vendedores',
                        'technical_info' => 'Laravel, Vue.js, API de pagos'
                    ]
                ],
                [
                    'sku' => '9010',
                    'name' => 'Aplicación Móvil Híbrida',
                    'description' => 'Desarrollo de app móvil multiplataforma',
                    'price' => 2999.99,
                    'category' => 'Software',
                    'is_promo' => true,
                    'promo_price' => 2499.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'iOS y Android',
                        'Diseño nativo',
                        'Backend completo',
                        'Notificaciones push',
                        'Integración con APIs',
                        'Panel de administración'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Multiplataforma, Experiencia nativa',
                        'technical_info' => 'Flutter, Laravel, Firebase'
                    ]
                ],
                [
                    'sku' => '9011',
                    'name' => 'Sistema de Reservas',
                    'description' => 'Plataforma de reservas y citas online',
                    'price' => 799.99,
                    'category' => 'Software',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Calendario interactivo',
                        'Gestión de citas',
                        'Recordatorios automáticos',
                        'Pagos online',
                        'App móvil',
                        'Reportes y estadísticas'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Automatización, Gestión eficiente',
                        'technical_info' => 'Laravel, Vue.js, API de pagos'
                    ]
                ],
                [
                    'sku' => '9012',
                    'name' => 'Automatización de Marketing',
                    'description' => 'Sistema completo de marketing digital',
                    'price' => 599.99,
                    'category' => 'Automatización',
                    'is_promo' => true,
                    'promo_price' => 499.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Email marketing',
                        'Automatización de redes',
                        'Segmentación avanzada',
                        'Analytics en tiempo real',
                        'Integración con CRM',
                        'Plantillas personalizables'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Marketing automatizado, ROI mejorado',
                        'technical_info' => 'API completa, Integración total'
                    ]
                ],
                [
                    'sku' => '9013',
                    'name' => 'Sistema de Gestión Escolar',
                    'description' => 'Plataforma completa para instituciones educativas',
                    'price' => 1499.99,
                    'category' => 'Software',
                    'is_promo' => false,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                    'characteristics' => [
                        'Gestión de estudiantes',
                        'Control de calificaciones',
                        'Comunicación padres-profesores',
                        'Asistencia digital',
                        'Reportes académicos',
                        'App móvil para padres'
                    ],
                    'metadata' => [
                        'quantity' => '1 proyecto',
                        'type' => 'Servicio',
                        'format' => 'Pago único',
                        'benefits' => 'Gestión educativa, Comunicación eficiente',
                        'technical_info' => 'Laravel, Vue.js, MySQL'
                    ]
                ],
                [
                    'sku' => '9014',
                    'name' => 'Chatbot con IA Avanzada',
                    'description' => 'Solución de chatbot con inteligencia artificial',
                    'price' => 299.99,
                    'category' => 'Chatbots',
                    'is_promo' => true,
                    'promo_price' => 249.99,
                    'image' => 'https://nutrientpark.com/wp-content/uploads/2025/05/promocionar.png',
                    'characteristics' => [
                        'IA conversacional',
                        'Aprendizaje automático',
                        'Integración con CRM',
                        'Análisis de sentimientos',
                        'Múltiples idiomas',
                        'Personalización avanzada'
                    ],
                    'metadata' => [
                        'quantity' => '1 licencia',
                        'type' => 'SaaS',
                        'format' => 'Suscripción mensual',
                        'benefits' => 'Atención inteligente, Escalabilidad',
                        'technical_info' => 'OpenAI, API de WhatsApp'
                    ]
                ]
            ];

            foreach ($products as $product) {
                try {
                    // Get or create menu item for the category
                    $menuItem = WhatsappMenuItem::firstOrCreate(
                        [
                            'menu_id' => $mainMenu->id,
                            'action_id' => strtolower(str_replace(' ', '_', $product['category']))
                        ],
                        [
                            'title' => $product['category'],
                            'icon' => '📦',
                           'description' => 'Productos de ' . $product['category'],
                             'order' => 1,
                            'is_active' => true
                        ]
                    );

                    // Ensure the name is not longer than 24 characters
                    $name = Str::limit($product['name'], 24, '');

                    // Create or update the price
                    try {
                        $price = WhatsappPrice::updateOrCreate(
                            ['sku' => $product['sku']],
                            [
                                'menu_item_id' => $menuItem->id,
                                'category' => $product['category'],
                                'name' => $name,
                                'description' => $product['description'],
                                'price' => $product['price'],
                                'promo_price' => $product['promo_price'] ?? null,
                                'is_promo' => $product['is_promo'] ?? false,
                                'promo_start_date' => $product['is_promo'] ? now() : null,
                                'promo_end_date' => $product['is_promo'] ? now()->addDays(30) : null,
                                'currency' => 'USD',
                                'is_active' => in_array($product['category'], ['Control de Peso', 'Bienestar Digestivo', 'Vitaminas', 'Proteínas', 'Combos', 'Kits Especiales', 'Combos Desayuno']) ? false : true,
                                'stock' => $product['stock'] ?? 100,
                                'allow_quantity_selection' => $product['allow_quantity_selection'] ?? true,
                                'min_quantity' => $product['min_quantity'] ?? 1,
                                'max_quantity' => $product['max_quantity'] ?? 999,
                                'image' => $product['image'] ?? 'https://nutrientpark.com/wp-content/uploads/2025/05/logo.png',
                                'characteristics' => isset($product['characteristics']) ? json_encode($product['characteristics']) : json_encode([]),
                                'metadata' => json_encode($product['metadata'])
                            ]
                        );

                        // Log para verificar que los datos se guardaron correctamente
                        Log::info("Producto creado/actualizado", [
                            'sku' => $product['sku'],
                            'name' => $name,
                            'image' => $price->image,
                            'characteristics' => $price->characteristics
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error al crear/actualizar producto {$product['sku']}", [
                            'error' => $e->getMessage(),
                            'product_data' => $product
                        ]);
                        continue;
                    }
                } catch (\Exception $e) {
                    Log::error("Error creating product {$product['sku']}: " . $e->getMessage());
                    continue;
                }
            }
        } catch (\Exception $e) {
            Log::error("Error in WhatsappPricesSeeder: " . $e->getMessage());
            throw $e;
        }
    }
}
