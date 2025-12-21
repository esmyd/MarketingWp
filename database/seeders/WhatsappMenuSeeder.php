<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappMenu;
use App\Models\WhatsappBusinessProfile;

class WhatsappMenuSeeder extends Seeder
{
    public function run()
    {
        // Obtener el primer perfil de negocio
        $businessProfile = WhatsappBusinessProfile::first();

        if (!$businessProfile) {
            $this->command->error('No se encontró ningún perfil de negocio. Por favor, crea uno primero.');
            return;
        }

        // Menú Principal
        WhatsappMenu::create([
            'action_id' => 'main_menu',
            'title' => 'Menú Principal',
            'button_text' => '🏠 Menú Principal',
            'content' => '¡Bienvenido! ¿En qué puedo ayudarte hoy?',
            'is_active' => true,
            'business_profile_id' => $businessProfile->id,
            'metadata' => [
                'sections' => []
            ]
        ]);

        // Menú de Productos
        WhatsappMenu::create([
            'action_id' => 'menu_productos',
            'title' => 'Productos',
            'button_text' => '🛍️ Productos',
            'content' => 'Explora nuestro catálogo de productos',
            'is_active' => true,
            'business_profile_id' => $businessProfile->id,
            'metadata' => [
                'sections' => []
            ]
        ]);

        // Menú de Pedidos
        WhatsappMenu::create([
            'action_id' => 'menu_pedido',
            'title' => 'Ver Pedidos',
            'button_text' => '📦 Ver Pedidos',
            'content' => 'Consulta el estado de tus pedidos',
            'is_active' => true,
            'business_profile_id' => $businessProfile->id,
            'metadata' => [
                'sections' => []
            ]
        ]);

        // Menú de Información
        WhatsappMenu::create([
            'action_id' => 'menu_info',
            'title' => 'Información',
            'button_text' => 'ℹ️ Información',
            'content' => 'Información importante sobre nuestros servicios',
            'is_active' => true,
            'business_profile_id' => $businessProfile->id,
            'metadata' => [
                'sections' => [
                    [
                        'title' => 'Información General',
                        'rows' => [
                            [
                                'id' => 'horarios',
                                'title' => 'Horarios de Atención',
                                'description' => 'Conoce nuestros horarios de servicio'
                            ],
                            [
                                'id' => 'contacto',
                                'title' => 'Información de Contacto',
                                'description' => 'Datos para contactarnos'
                            ],
                            [
                                'id' => 'envios',
                                'title' => 'Información de Envíos',
                                'description' => 'Detalles sobre nuestros envíos'
                            ],
                            [
                                'id' => 'pagos',
                                'title' => 'Métodos de Pago',
                                'description' => 'Formas de pago aceptadas'
                            ],
                            [
                                'id' => 'asesoria',
                                'title' => 'Asesoría',
                                'description' => 'Obtén ayuda personalizada'
                            ],
                            [
                                'id' => 'redes',
                                'title' => 'Redes Sociales',
                                'description' => 'Síguenos en redes sociales'
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        // Menú de Precios
        WhatsappMenu::create([
            'action_id' => 'prices_menu',
            'title' => 'Catálogo de Precios',
            'button_text' => '💰 Ver Precios',
            'content' => "🛍️ *Catálogo de Productos*\n\n" .
                        "Aquí encontrarás nuestro catálogo completo de productos.\n" .
                        "Puedes explorar las diferentes categorías y seleccionar los productos que te interesen.\n\n" .
                        "También puedes escribir el código del producto (ej: 1001) para verlo directamente.",
            'is_active' => true,
            'business_profile_id' => $businessProfile->id,
            'metadata' => [
                'sections' => []
            ]
        ]);
    }
}
