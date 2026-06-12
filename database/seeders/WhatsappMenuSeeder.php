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

        WhatsappMenu::updateOrCreate(
            ['action_id' => 'main_menu'],
            [
                'title'               => 'Menú Principal',
                'button_text'         => '🏠 Menú Principal',
                'content'             => '¡Bienvenido a Siglo Tecnológico! ¿En qué puedo ayudarte hoy?',
                'is_active'           => true,
                'business_profile_id' => $businessProfile->id,
                'metadata'            => ['sections' => []],
            ]
        );

        WhatsappMenu::updateOrCreate(
            ['action_id' => 'menu_productos'],
            [
                'title'               => 'Soluciones',
                'button_text'         => '💻 Soluciones',
                'content'             => 'Explora nuestro catálogo de soluciones tecnológicas',
                'is_active'           => true,
                'business_profile_id' => $businessProfile->id,
                'metadata'            => ['sections' => []],
            ]
        );

        WhatsappMenu::updateOrCreate(
            ['action_id' => 'menu_pedido'],
            [
                'title'               => 'Ver Pedidos',
                'button_text'         => '📦 Ver Pedidos',
                'content'             => 'Consulta el estado de tus proyectos y pedidos',
                'is_active'           => true,
                'business_profile_id' => $businessProfile->id,
                'metadata'            => ['sections' => []],
            ]
        );

        WhatsappMenu::updateOrCreate(
            ['action_id' => 'info_menu'],
            [
                'title'               => 'Información',
                'button_text'         => 'ℹ️ Ver opciones',
                'content'             => "ℹ️ *Información de Siglo Tecnológico*\n\nSelecciona el tema que necesitas:",
                'is_active'           => true,
                'business_profile_id' => $businessProfile->id,
                'metadata'            => ['sections' => []],
            ]
        );

        WhatsappMenu::updateOrCreate(
            ['action_id' => 'menu_info'],
            [
                'title'               => 'Información',
                'button_text'         => 'ℹ️ Información',
                'content'             => 'Información sobre Siglo Tecnológico S.A.',
                'is_active'           => true,
                'business_profile_id' => $businessProfile->id,
                'metadata'            => [
                    'sections' => [
                        [
                            'title' => 'Información General',
                            'rows'  => [
                                ['id' => 'horarios',  'title' => 'Horarios de Atención',  'description' => 'Nuestros horarios de servicio'],
                                ['id' => 'contacto',  'title' => 'Información de Contacto','description' => 'Teléfono, email y dirección'],
                                ['id' => 'envios',    'title' => 'Entregas y Proyectos',   'description' => 'Tiempos de entrega'],
                                ['id' => 'pagos',     'title' => 'Métodos de Pago',        'description' => 'Formas de pago aceptadas'],
                                ['id' => 'asesoria',  'title' => 'Asesoría Tecnológica',   'description' => 'Consulta personalizada gratuita'],
                                ['id' => 'redes',     'title' => 'Redes Sociales',         'description' => 'Síguenos en redes'],
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Menú de Precios — usar updateOrCreate para no duplicar (WhatsappPricesSeeder lo gestiona)
        WhatsappMenu::updateOrCreate(
            ['action_id' => 'prices_menu'],
            [
                'title'               => 'Catálogo de Soluciones',
                'button_text'         => '💻 Ver Soluciones',
                'content'             => "🛍️ *Catálogo de Soluciones Tecnológicas*\n\nSelecciona una categoría para explorar nuestros servicios y precios.",
                'is_active'           => true,
                'business_profile_id' => $businessProfile->id,
                'metadata'            => ['sections' => []],
            ]
        );
    }
}
