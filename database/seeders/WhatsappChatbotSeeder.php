<?php

namespace Database\Seeders;

use App\Models\WhatsappBusinessProfile;
use App\Models\WhatsappChatbotConfig;
use App\Models\WhatsappMenu;
use App\Models\WhatsappMenuItem;
use App\Models\WhatsappPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class WhatsappChatbotSeeder extends Seeder
{
    public function run(): void
    {
        $businessProfile = WhatsappBusinessProfile::first();
        if (!$businessProfile) {
            Log::error('No se encontró el perfil de WhatsApp Business');
            return;
        }

        // Crear configuración del chatbot
        $config = WhatsappChatbotConfig::create([
            'business_profile_id' => $businessProfile->id,
            'welcome_message' => '¡Hola! 👋 Bienvenido a Herbalife. ¿En qué puedo ayudarte hoy?',
            'default_response' => 'Lo siento, no entendí tu mensaje. Por favor, selecciona una opción del menú.',
            'greetings' => ['hola', 'hi', 'buenos dias', 'buenas tardes', 'buenas noches'],
            'menu_commands' => ['menu', 'opciones', 'ayuda'],
            'metadata' => [
                'language' => 'es',
                'timezone' => 'America/Bogota'
            ],
            'is_active' => true
        ]);

        // Crear menú principal
        $mainMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Menú Principal',
            'description' => 'Selecciona una opción',
            'type' => 'button',
            'content' => '¿Qué te gustaría conocer?',
            'button_text' => 'Ver opciones',
            'icon' => '📱',
            'action_id' => 'main_menu',
            'order' => 1,
            'is_active' => true,
            'metadata' => [
                'style' => 'primary',
                'max_buttons' => 3
            ]
        ]);

        // Crear items del menú principal
        $mainMenuItems = [
            [
                'title' => '🛍️ Productos',
                'description' => 'Ver catálogo y precios',
                'action_id' => 'productos',
                'icon' => '🛍️',
                'order' => 1
            ],
            [
                'title' => '🛒 Pedidos',
                'description' => 'Realiza tu pedido aquí',
                'action_id' => 'pedido',
                'icon' => '🛒',
                'order' => 2
            ],
            [
                'title' => 'ℹ️ Más Información',
                'description' => 'Horarios, asesoría y más',
                'action_id' => 'mas_info',
                'icon' => 'ℹ️',
                'order' => 3
            ]
        ];

        foreach ($mainMenuItems as $item) {
            WhatsappMenuItem::create([
                'menu_id' => $mainMenu->id,
                'title' => $item['title'],
                'description' => $item['description'],
                'action_id' => $item['action_id'],
                'icon' => $item['icon'],
                'order' => $item['order'],
                'is_active' => true
            ]);
        }

        // Menú de información
        WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'action_id' => 'info_menu',
            'title' => 'ℹ️ Más Información',
            'type' => 'list',
            'content' => 'Selecciona una opción para obtener más información:',
            'button_text' => 'Ver opciones',
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Información',
                        'rows' => [
                            [
                                'id' => 'horarios',
                                'title' => '🕒 Horarios y Ubicación',
                                'description' => 'Conoce nuestros horarios y ubicación'
                            ],
                            [
                                'id' => 'asesoria',
                                'title' => '👨‍💼 Asesoría Personalizada',
                                'description' => 'Obtén ayuda personalizada'
                            ],
                            [
                                'id' => 'pagos',
                                'title' => '💳 Métodos de Pago',
                                'description' => 'Conoce nuestras formas de pago'
                            ],
                            [
                                'id' => 'envios',
                                'title' => '🚚 Envíos y Entregas',
                                'description' => 'Información sobre envíos'
                            ],
                            [
                                'id' => 'faq',
                                'title' => '❓ Preguntas Frecuentes',
                                'description' => 'Resuelve tus dudas'
                            ],
                            [
                                'id' => 'contacto',
                                'title' => '📞 Contacto',
                                'description' => 'Nuestros datos de contacto'
                            ],
                            [
                                'id' => 'redes',
                                'title' => '📱 Redes Sociales',
                                'description' => 'Síguenos en redes sociales'
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    'horarios' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "🕒 *Horarios de Atención*\n\n" .
                                "Lunes a Viernes: 9:00 AM - 6:00 PM\n" .
                                "Sábados: 9:00 AM - 1:00 PM\n" .
                                "Domingos: Cerrado\n\n" .
                                "📍 *Ubicación*\n" .
                                "Av. Principal #123, Ciudad"
                        ]
                    ],
                    'asesoria' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                                "Ofrecemos asesoría en:\n" .
                                "• Nutrición\n" .
                                "• Productos\n" .
                                "• Negocios\n\n" .
                                "📞 *Contacto Directo*\n" .
                                "098-765-4321"
                        ]
                    ],
                    'pagos' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "💳 *Métodos de Pago*\n\n" .
                                "Aceptamos:\n" .
                                "• Transferencia bancaria\n" .
                                "• Efectivo\n" .
                                "• Tarjetas de crédito/débito"
                        ]
                    ],
                    'envios' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "🚚 *Envíos y Entregas*\n\n" .
                                "• Envío estándar: 2-3 días\n" .
                                "• Envío express: 24 horas\n" .
                                "• Retiro en tienda: Gratis"
                        ]
                    ],
                    'faq' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "❓ *Preguntas Frecuentes*\n\n" .
                                "1. ¿Tienen garantía?\n" .
                                "Sí, todos nuestros productos tienen garantía.\n\n" .
                                "2. ¿Cómo puedo devolver un producto?\n" .
                                "Tienes 15 días para devoluciones."
                        ]
                    ],
                    'contacto' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "📞 *Contacto*\n\n" .
                                "WhatsApp: 098-765-4321\n" .
                                "Teléfono: 02-123-4567\n" .
                                "Email: info@empresa.com"
                        ]
                    ],
                    'redes' => [
                        'type' => 'text',
                        'text' => [
                            'body' => "📱 *Redes Sociales*\n\n" .
                                "Instagram: @empresa\n" .
                                "Facebook: /empresa\n" .
                                "Twitter: @empresa\n" .
                                "YouTube: /empresa"
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de precios
        $pricesMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Precios y Promos',
            'description' => 'Consulta precios y promos',
            'type' => 'list',
            'content' => 'Selecciona una categoría:',
            'button_text' => 'Ver Precios',
            'action_id' => 'prices_menu',
            'is_active' => true,
            'order' => 2
        ]);

        // Crear categorías de precios
        $categories = [
            [
                'title' => '🥗 Nutrición',
                'description' => 'Productos nutricionales y suplementos',
                'action_id' => 'precios_nutricion',
                'icon' => '🥗',
                'order' => 1
            ],
            [
                'title' => '💆 Bienestar',
                'description' => 'Productos para el bienestar general',
                'action_id' => 'precios_bienestar',
                'icon' => '💆',
                'order' => 2
            ],
            [
                'title' => '🧴 Cuidado',
                'description' => 'Productos de cuidado personal',
                'action_id' => 'precios_cuidado',
                'icon' => '🧴',
                'order' => 3
            ],
            [
                'title' => '⚡ Energía',
                'description' => 'Productos energéticos',
                'action_id' => 'precios_energia',
                'icon' => '⚡',
                'order' => 4
            ]
        ];

        foreach ($categories as $item) {
            $menuItem = WhatsappMenuItem::create([
                'menu_id' => $pricesMenu->id,
                'title' => $item['title'],
                'description' => $item['description'],
                'action_id' => $item['action_id'],
                'icon' => $item['icon'],
                'order' => $item['order'],
                'is_active' => true
            ]);

            // Crear precios para cada categoría
            switch ($item['action_id']) {
                case 'precios_nutricion':
                    $this->createNutritionPrices($menuItem);
                    break;
                case 'precios_bienestar':
                    $this->createWellnessPrices($menuItem);
                    break;
                case 'precios_cuidado':
                    $this->createPersonalCarePrices($menuItem);
                    break;
                case 'precios_energia':
                    $this->createEnergyPrices($menuItem);
                    break;
            }
        }

        // Crear menú de productos
        $productsMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Catálogo de Productos',
            'description' => 'Selecciona una categoría',
            'type' => 'list',
            'content' => 'Nuestras categorías de productos',
            'button_text' => 'Ver categorías',
            'icon' => '🛍️',
            'action_id' => 'products_menu',
            'order' => 2,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Categorías',
                        'rows' => [
                            [
                                'id' => 'nutricion',
                                'title' => '🥗 Nutrición',
                                'description' => 'Productos nutricionales y suplementos'
                            ],
                            [
                                'id' => 'bienestar',
                                'title' => '💪 Bienestar',
                                'description' => 'Productos para el bienestar general'
                            ],
                            [
                                'id' => 'cuidado_personal',
                                'title' => '🧴 Cuidado Personal',
                                'description' => 'Productos de cuidado personal y belleza'
                            ],
                            [
                                'id' => 'energia',
                                'title' => '⚡ Energía',
                                'description' => 'Productos energéticos y rendimiento'
                            ],
                            [
                                'id' => 'control_peso',
                                'title' => '⚖️ Control de Peso',
                                'description' => 'Productos para el control de peso'
                            ],
                            [
                                'id' => 'deportivo',
                                'title' => '🏃 Deportivo',
                                'description' => 'Productos para deportistas'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de nutrición
        $nutritionMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Productos de Nutrición',
            'description' => 'Selecciona una subcategoría',
            'type' => 'list',
            'content' => 'Nuestros productos nutricionales',
            'button_text' => 'Ver productos',
            'icon' => '🥗',
            'action_id' => 'nutrition_menu',
            'order' => 4,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Batidos Nutricionales',
                        'rows' => [
                            [
                                'id' => 'f1_vanilla',
                                'title' => '🥤 Fórmula 1 Vainilla',
                                'description' => 'Batido nutricional sabor vainilla',
                                'price' => 45.99,
                                'discount' => 5,
                                'category' => 'Batidos Nutricionales'
                            ],
                            [
                                'id' => 'f1_chocolate',
                                'title' => '🥤 Fórmula 1 Chocolate',
                                'description' => 'Batido nutricional sabor chocolate',
                                'price' => 45.99,
                                'discount' => 0,
                                'category' => 'Batidos Nutricionales'
                            ],
                            [
                                'id' => 'f1_fresa',
                                'title' => '🥤 Fórmula 1 Fresa',
                                'description' => 'Batido nutricional sabor fresa',
                                'price' => 45.99,
                                'discount' => 0,
                                'category' => 'Batidos Nutricionales'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Snacks Saludables',
                        'rows' => [
                            [
                                'id' => 'protein_bar_chocolate',
                                'title' => '🍫 Barra Proteica Chocolate',
                                'description' => 'Barras de proteína sabor chocolate',
                                'price' => 25.99,
                                'discount' => 10,
                                'category' => 'Snacks Saludables'
                            ],
                            [
                                'id' => 'protein_bar_fresa',
                                'title' => '🍫 Barra Proteica Fresa',
                                'description' => 'Barras de proteína sabor fresa',
                                'price' => 25.99,
                                'discount' => 0,
                                'category' => 'Snacks Saludables'
                            ],
                            [
                                'id' => 'protein_chips_original',
                                'title' => '🥔 Chips Proteicos Original',
                                'description' => 'Snacks proteicos sabor original'
                            ],
                            [
                                'id' => 'protein_chips_bbq',
                                'title' => '🥔 Chips Proteicos BBQ',
                                'description' => 'Snacks proteicos sabor BBQ'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Bebidas Nutricionales',
                        'rows' => [
                            [
                                'id' => 'tea_original',
                                'title' => '🍵 Té Concentrado Original',
                                'description' => 'Té concentrado herbal original'
                            ],
                            [
                                'id' => 'tea_limon',
                                'title' => '🍵 Té Concentrado Limón',
                                'description' => 'Té concentrado herbal limón'
                            ],
                            [
                                'id' => 'aloe_original',
                                'title' => '🌿 Aloe Vera Original',
                                'description' => 'Bebida de aloe vera original'
                            ],
                            [
                                'id' => 'aloe_mango',
                                'title' => '🌿 Aloe Vera Mango',
                                'description' => 'Bebida de aloe vera mango'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de pedidos
        $orderMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Realizar Pedido',
            'description' => 'Selecciona los productos para tu pedido',
            'type' => 'list',
            'content' => 'Realiza tu pedido paso a paso',
            'button_text' => 'Iniciar pedido',
            'icon' => '🛒',
            'action_id' => 'order_menu',
            'order' => 5,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Pasos del Pedido',
                        'rows' => [
                            [
                                'id' => 'seleccion_productos',
                                'title' => '1️⃣ Seleccionar Productos',
                                'description' => 'Elige los productos para tu pedido'
                            ],
                            [
                                'id' => 'confirmar_cantidades',
                                'title' => '2️⃣ Confirmar Cantidades',
                                'description' => 'Especifica las cantidades de cada producto'
                            ],
                            [
                                'id' => 'metodo_pago',
                                'title' => '3️⃣ Método de Pago',
                                'description' => 'Elige cómo deseas pagar'
                            ],
                            [
                                'id' => 'confirmar_direccion',
                                'title' => '4️⃣ Confirmar Dirección',
                                'description' => 'Verifica la dirección de entrega'
                            ],
                            [
                                'id' => 'finalizar_pedido',
                                'title' => '5️⃣ Finalizar Pedido',
                                'description' => 'Confirma y finaliza tu pedido'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de bienestar
        $wellnessMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Productos de Bienestar',
            'description' => 'Selecciona una subcategoría',
            'type' => 'list',
            'content' => 'Nuestros productos de bienestar',
            'button_text' => 'Ver productos',
            'icon' => '💪',
            'action_id' => 'wellness_menu',
            'order' => 6,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Suplementos',
                        'rows' => [
                            [
                                'id' => 'multivitamin_complete',
                                'title' => '💊 Multivitamínico Completo',
                                'description' => 'Complejo multivitamínico completo'
                            ],
                            [
                                'id' => 'multivitamin_women',
                                'title' => '💊 Multivitamínico Mujer',
                                'description' => 'Multivitamínico específico para mujeres'
                            ],
                            [
                                'id' => 'multivitamin_men',
                                'title' => '💊 Multivitamínico Hombre',
                                'description' => 'Multivitamínico específico para hombres'
                            ],
                            [
                                'id' => 'omega3_1000',
                                'title' => '🐟 Omega 3 1000mg',
                                'description' => 'Ácidos grasos esenciales 1000mg'
                            ],
                            [
                                'id' => 'omega3_2000',
                                'title' => '🐟 Omega 3 2000mg',
                                'description' => 'Ácidos grasos esenciales 2000mg'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Cuidado Digestivo',
                        'rows' => [
                            [
                                'id' => 'probiotic_complete',
                                'title' => '🦠 Probiótico Completo',
                                'description' => 'Complejo probiótico completo'
                            ],
                            [
                                'id' => 'digestive_enzymes',
                                'title' => '🦠 Enzimas Digestivas',
                                'description' => 'Complejo de enzimas digestivas'
                            ],
                            [
                                'id' => 'fiber_complex',
                                'title' => '🌾 Complejo de Fibra',
                                'description' => 'Suplemento de fibra dietética'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Antioxidantes',
                        'rows' => [
                            [
                                'id' => 'vitamin_c',
                                'title' => '🍊 Vitamina C',
                                'description' => 'Suplemento de vitamina C'
                            ],
                            [
                                'id' => 'vitamin_e',
                                'title' => '🌰 Vitamina E',
                                'description' => 'Suplemento de vitamina E'
                            ],
                            [
                                'id' => 'coenzyme_q10',
                                'title' => '⚡ Coenzima Q10',
                                'description' => 'Suplemento de coenzima Q10'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de cuidado personal
        $personalCareMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Cuidado Personal',
            'description' => 'Selecciona una subcategoría',
            'type' => 'list',
            'content' => 'Nuestros productos de cuidado personal',
            'button_text' => 'Ver productos',
            'icon' => '🧴',
            'action_id' => 'personal_care_menu',
            'order' => 7,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Cuidado Facial',
                        'rows' => [
                            [
                                'id' => 'facial_cleanser',
                                'title' => '🧼 Limpiador Facial',
                                'description' => 'Limpiador facial suave'
                            ],
                            [
                                'id' => 'facial_toner',
                                'title' => '💧 Tónico Facial',
                                'description' => 'Tónico facial refrescante'
                            ],
                            [
                                'id' => 'facial_moisturizer',
                                'title' => '💧 Crema Hidratante',
                                'description' => 'Crema hidratante facial'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Cuidado Corporal',
                        'rows' => [
                            [
                                'id' => 'body_wash',
                                'title' => '🚿 Gel de Baño',
                                'description' => 'Gel de baño hidratante'
                            ],
                            [
                                'id' => 'body_lotion',
                                'title' => '🧴 Loción Corporal',
                                'description' => 'Loción corporal hidratante'
                            ],
                            [
                                'id' => 'hand_cream',
                                'title' => '🤲 Crema de Manos',
                                'description' => 'Crema hidratante para manos'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Cuidado Capilar',
                        'rows' => [
                            [
                                'id' => 'shampoo',
                                'title' => '🧴 Shampoo',
                                'description' => 'Shampoo nutritivo'
                            ],
                            [
                                'id' => 'conditioner',
                                'title' => '🧴 Acondicionador',
                                'description' => 'Acondicionador reparador'
                            ],
                            [
                                'id' => 'hair_mask',
                                'title' => '💆‍♀️ Mascarilla Capilar',
                                'description' => 'Mascarilla capilar nutritiva'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de energía
        $energyMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Productos Energéticos',
            'description' => 'Selecciona una subcategoría',
            'type' => 'list',
            'content' => 'Nuestros productos energéticos',
            'button_text' => 'Ver productos',
            'icon' => '⚡',
            'action_id' => 'energy_menu',
            'order' => 8,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Bebidas Energéticas',
                        'rows' => [
                            [
                                'id' => 'lifelift_original',
                                'title' => '🥤 Liftoff Original',
                                'description' => 'Bebida energética original'
                            ],
                            [
                                'id' => 'lifelift_citrus',
                                'title' => '🥤 Liftoff Cítrico',
                                'description' => 'Bebida energética sabor cítrico'
                            ],
                            [
                                'id' => 'lifelift_berry',
                                'title' => '🥤 Liftoff Berry',
                                'description' => 'Bebida energética sabor berry'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Pre-entrenamiento',
                        'rows' => [
                            [
                                'id' => 'cr7_drive',
                                'title' => '⚡ CR7 Drive',
                                'description' => 'Bebida pre-entrenamiento'
                            ],
                            [
                                'id' => 'cr7_drive_plus',
                                'title' => '⚡ CR7 Drive Plus',
                                'description' => 'Bebida pre-entrenamiento plus'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Recuperación',
                        'rows' => [
                            [
                                'id' => 'rebuild_strength',
                                'title' => '💪 Rebuild Strength',
                                'description' => 'Bebida de recuperación muscular'
                            ],
                            [
                                'id' => 'rebuild_endurance',
                                'title' => '🏃 Rebuild Endurance',
                                'description' => 'Bebida de recuperación energética'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú de control de peso
        $weightControlMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Control de Peso',
            'description' => 'Selecciona una subcategoría',
            'type' => 'list',
            'content' => 'Nuestros productos para control de peso',
            'button_text' => 'Ver productos',
            'icon' => '⚖️',
            'action_id' => 'weight_control_menu',
            'order' => 9,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Programas de Control de Peso',
                        'rows' => [
                            [
                                'id' => 'weight_loss_program',
                                'title' => '📋 Programa Pérdida de Peso',
                                'description' => 'Programa completo para pérdida de peso'
                            ],
                            [
                                'id' => 'weight_maintenance',
                                'title' => '📋 Programa Mantenimiento',
                                'description' => 'Programa para mantener el peso'
                            ],
                            [
                                'id' => 'weight_gain',
                                'title' => '📋 Programa Aumento de Peso',
                                'description' => 'Programa para aumento de peso saludable'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Productos Específicos',
                        'rows' => [
                            [
                                'id' => 'thermo_complete',
                                'title' => '🔥 Thermo Complete',
                                'description' => 'Suplemento termogénico completo'
                            ],
                            [
                                'id' => 'cell_activator',
                                'title' => '⚡ Cell Activator',
                                'description' => 'Activador celular metabólico'
                            ],
                            [
                                'id' => 'total_control',
                                'title' => '🎯 Total Control',
                                'description' => 'Control del apetito y energía'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Crear menú deportivo
        $sportsMenu = WhatsappMenu::create([
            'business_profile_id' => $businessProfile->id,
            'title' => 'Productos Deportivos',
            'description' => 'Selecciona una subcategoría',
            'type' => 'list',
            'content' => 'Nuestros productos para deportistas',
            'button_text' => 'Ver productos',
            'icon' => '🏃',
            'action_id' => 'sports_menu',
            'order' => 10,
            'is_active' => true,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Proteínas',
                        'rows' => [
                            [
                                'id' => 'protein_powder',
                                'title' => '🥛 Proteína en Polvo',
                                'description' => 'Proteína de suero de leche'
                            ],
                            [
                                'id' => 'protein_ready',
                                'title' => '🥛 Proteína Lista',
                                'description' => 'Bebida proteica lista para tomar'
                            ],
                            [
                                'id' => 'protein_bar_sport',
                                'title' => '🍫 Barra Proteica Deportiva',
                                'description' => 'Barra proteica para deportistas'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Aminoácidos',
                        'rows' => [
                            [
                                'id' => 'bcaa',
                                'title' => '💪 BCAA',
                                'description' => 'Aminoácidos de cadena ramificada'
                            ],
                            [
                                'id' => 'glutamine',
                                'title' => '💪 Glutamina',
                                'description' => 'Aminoácido glutamina'
                            ],
                            [
                                'id' => 'arginine',
                                'title' => '💪 Arginina',
                                'description' => 'Aminoácido arginina'
                            ]
                        ]
                    ],
                    [
                        'title' => 'Rendimiento',
                        'rows' => [
                            [
                                'id' => 'creatine',
                                'title' => '⚡ Creatina',
                                'description' => 'Suplemento de creatina'
                            ],
                            [
                                'id' => 'beta_alanine',
                                'title' => '⚡ Beta Alanina',
                                'description' => 'Suplemento de beta alanina'
                            ],
                            [
                                'id' => 'pre_workout',
                                'title' => '⚡ Pre-Workout',
                                'description' => 'Suplemento pre-entrenamiento'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    private function createNutritionPrices($menuItem)
    {
        $prices = [
            [
                'category' => 'Nutrición',
                'name' => 'Fórmula 1 Vainilla',
                'description' => 'Batido nutricional con proteína de soya y 23 vitaminas y minerales esenciales',
                'quantity' => '550g',
                'flavor' => 'Vainilla',
                'format' => 'Polvo',
                'benefits' => 'Control de peso, Energía, Nutrición balanceada',
                'nutritional_info' => 'Proteína: 18g, Carbohidratos: 24g, Grasas: 3g, Calorías: 200',
                'price' => 45.99,
                'promo_price' => 39.99,
                'is_promo' => true,
                'promo_start_date' => now(),
                'promo_end_date' => now()->addDays(30)
            ],
            [
                'category' => 'Nutrición',
                'name' => 'Fórmula 1 Chocolate',
                'description' => 'Batido nutricional con proteína de soya y 23 vitaminas y minerales esenciales',
                'quantity' => '550g',
                'flavor' => 'Chocolate',
                'format' => 'Polvo',
                'benefits' => 'Control de peso, Energía, Nutrición balanceada',
                'nutritional_info' => 'Proteína: 18g, Carbohidratos: 24g, Grasas: 3g, Calorías: 200',
                'price' => 45.99,
                'is_promo' => false
            ],
            [
                'category' => 'Nutrición',
                'name' => 'Fórmula 1 Fresa',
                'description' => 'Batido nutricional con proteína de soya y 23 vitaminas y minerales esenciales',
                'quantity' => '550g',
                'flavor' => 'Fresa',
                'format' => 'Polvo',
                'benefits' => 'Control de peso, Energía, Nutrición balanceada',
                'nutritional_info' => 'Proteína: 18g, Carbohidratos: 24g, Grasas: 3g, Calorías: 200',
                'price' => 45.99,
                'is_promo' => false
            ],
            [
                'category' => 'Nutrición',
                'name' => 'Fórmula 2 Proteína',
                'description' => 'Suplemento de proteína para complementar la Fórmula 1',
                'quantity' => '300g',
                'flavor' => 'Natural',
                'format' => 'Polvo',
                'benefits' => 'Masa muscular, Recuperación, Nutrición celular',
                'nutritional_info' => 'Proteína: 15g, Carbohidratos: 2g, Grasas: 1g, Calorías: 80',
                'price' => 55.99,
                'is_promo' => false
            ],
            [
                'category' => 'Nutrición',
                'name' => 'Fórmula 3 Fibra',
                'description' => 'Suplemento de fibra y hierbas para la digestión',
                'quantity' => '180 tabletas',
                'flavor' => 'Natural',
                'format' => 'Tabletas',
                'benefits' => 'Digestión, Regularidad intestinal, Bienestar general',
                'nutritional_info' => 'Fibra: 5g, Calorías: 20',
                'price' => 35.99,
                'is_promo' => false
            ],
            [
                'category' => 'Nutrición',
                'name' => 'Proteína Personalizada',
                'description' => 'Mezcla de proteínas para personalizar tu batido',
                'quantity' => '300g',
                'flavor' => 'Natural',
                'format' => 'Polvo',
                'benefits' => 'Masa muscular, Recuperación, Nutrición personalizada',
                'nutritional_info' => 'Proteína: 20g, Carbohidratos: 1g, Grasas: 1g, Calorías: 90',
                'price' => 49.99,
                'is_promo' => false
            ]
        ];

        $this->createPrices($menuItem, $prices);
    }

    private function createWellnessPrices($menuItem)
    {
        $prices = [
            [
                'category' => 'Bienestar',
                'name' => 'Herbal Aloe Concentrado',
                'description' => 'Bebida concentrada de aloe vera para la digestión',
                'quantity' => '1L',
                'flavor' => 'Mango',
                'format' => 'Líquido',
                'benefits' => 'Digestión, Hidratación, Bienestar general',
                'nutritional_info' => 'Calorías: 10 por porción',
                'price' => 29.99,
                'is_promo' => false
            ],
            [
                'category' => 'Bienestar',
                'name' => 'RoseOx Antioxidante',
                'description' => 'Suplemento antioxidante con extracto de rosa mosqueta',
                'quantity' => '60 tabletas',
                'flavor' => 'Natural',
                'format' => 'Tabletas',
                'benefits' => 'Antioxidante, Piel saludable, Sistema inmunológico',
                'nutritional_info' => 'Vitamina C: 100mg, Calorías: 5',
                'price' => 39.99,
                'promo_price' => 34.99,
                'is_promo' => true,
                'promo_start_date' => now(),
                'promo_end_date' => now()->addDays(15)
            ],
            [
                'category' => 'Bienestar',
                'name' => 'Herbalifeline',
                'description' => 'Suplemento de ácidos grasos omega-3',
                'quantity' => '30 cápsulas',
                'flavor' => 'Natural',
                'format' => 'Cápsulas',
                'benefits' => 'Salud cardiovascular, Función cerebral, Inflamación',
                'nutritional_info' => 'Omega-3: 1g, Calorías: 10',
                'price' => 44.99,
                'is_promo' => false
            ],
            [
                'category' => 'Bienestar',
                'name' => 'Herbalife24 Rebuild Strength',
                'description' => 'Recuperación muscular post-entrenamiento',
                'quantity' => '750g',
                'flavor' => 'Chocolate',
                'format' => 'Polvo',
                'benefits' => 'Recuperación muscular, Energía, Nutrición deportiva',
                'nutritional_info' => 'Proteína: 20g, Carbohidratos: 30g, Calorías: 220',
                'price' => 59.99,
                'is_promo' => false
            ]
        ];

        $this->createPrices($menuItem, $prices);
    }

    private function createPersonalCarePrices($menuItem)
    {
        $prices = [
            [
                'category' => 'Cuidado Personal',
                'name' => 'Skin Activator',
                'description' => 'Activador de la piel para una apariencia más joven',
                'quantity' => '50ml',
                'flavor' => 'Natural',
                'format' => 'Gel',
                'benefits' => 'Piel joven, Hidratación, Tono uniforme',
                'nutritional_info' => 'Aplicación tópica',
                'price' => 49.99,
                'is_promo' => false
            ],
            [
                'category' => 'Cuidado Personal',
                'name' => 'Niteworks',
                'description' => 'Suplemento nocturno para la circulación',
                'quantity' => '30 sobres',
                'flavor' => 'Limón',
                'format' => 'Polvo',
                'benefits' => 'Circulación, Energía, Descanso',
                'nutritional_info' => 'L-arginina: 5g, Calorías: 15',
                'price' => 59.99,
                'is_promo' => false
            ],
            [
                'category' => 'Cuidado Personal',
                'name' => 'Herbal Aloe Gel',
                'description' => 'Gel de aloe vera para el cuidado de la piel',
                'quantity' => '150ml',
                'flavor' => 'Natural',
                'format' => 'Gel',
                'benefits' => 'Hidratación, Calmante, Piel saludable',
                'nutritional_info' => 'Aplicación tópica',
                'price' => 24.99,
                'is_promo' => false
            ],
            [
                'category' => 'Cuidado Personal',
                'name' => 'Herbalife SKIN',
                'description' => 'Línea completa de cuidado facial',
                'quantity' => 'Kit completo',
                'flavor' => 'Natural',
                'format' => 'Kit',
                'benefits' => 'Piel radiante, Anti-edad, Protección solar',
                'nutritional_info' => 'Aplicación tópica',
                'price' => 89.99,
                'is_promo' => false
            ]
        ];

        $this->createPrices($menuItem, $prices);
    }

    private function createEnergyPrices($menuItem)
    {
        $prices = [
            [
                'category' => 'Energía',
                'name' => 'Liftoff',
                'description' => 'Bebida energética con cafeína y vitaminas B',
                'quantity' => '10 sobres',
                'flavor' => 'Cítrico',
                'format' => 'Polvo',
                'benefits' => 'Energía, Concentración, Rendimiento',
                'nutritional_info' => 'Cafeína: 80mg, Calorías: 10',
                'price' => 19.99,
                'promo_price' => 15.99,
                'is_promo' => true,
                'promo_start_date' => now(),
                'promo_end_date' => now()->addDays(7)
            ],
            [
                'category' => 'Energía',
                'name' => 'NRG',
                'description' => 'Bebida energética natural con guaraná',
                'quantity' => '10 sobres',
                'flavor' => 'Berry',
                'format' => 'Polvo',
                'benefits' => 'Energía natural, Resistencia, Vitalidad',
                'nutritional_info' => 'Guaraná: 200mg, Calorías: 15',
                'price' => 24.99,
                'is_promo' => false
            ],
            [
                'category' => 'Energía',
                'name' => 'Herbalife24 CR7 Drive',
                'description' => 'Bebida energética para deportistas',
                'quantity' => '20 sobres',
                'flavor' => 'Frutas tropicales',
                'format' => 'Polvo',
                'benefits' => 'Energía deportiva, Resistencia, Recuperación',
                'nutritional_info' => 'Carbohidratos: 25g, Calorías: 100',
                'price' => 34.99,
                'is_promo' => false
            ],
            [
                'category' => 'Energía',
                'name' => 'Herbalife24 Hydrate',
                'description' => 'Bebida hidratante con electrolitos',
                'quantity' => '20 sobres',
                'flavor' => 'Lima-limón',
                'format' => 'Polvo',
                'benefits' => 'Hidratación, Electrolitos, Energía',
                'nutritional_info' => 'Electrolitos: 300mg, Calorías: 20',
                'price' => 29.99,
                'is_promo' => false
            ]
        ];

        $this->createPrices($menuItem, $prices);
    }

    private function createPrices($menuItem, $prices)
    {
        // Get the last used SKU to continue from there
        $lastSku = WhatsappPrice::orderBy('sku', 'desc')->first();
        $skuCounter = $lastSku ? (int)substr($lastSku->sku, 1) : 0;

        foreach ($prices as $price) {
            $skuCounter++;
            $sku = '1' . str_pad($skuCounter, 3, '0', STR_PAD_LEFT);

            $menuItem->prices()->create([
                'sku' => $sku,
                'category' => $menuItem->title,
                'name' => substr($price['name'], 0, 20), // Limitar nombre a 20 caracteres
                'description' => substr($price['description'] ?? '', 0, 72), // Limitar descripción a 72 caracteres
                'price' => $price['price'],
                'promo_price' => $price['promo_price'] ?? null,
                'is_promo' => $price['is_promo'] ?? false,
                'promo_start_date' => $price['is_promo'] ? now() : null,
                'promo_end_date' => $price['promo_end_date'] ?? null,
                'currency' => 'USD',
                'is_active' => true
            ]);
        }
    }
}
