<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappTemplate;

class WhatsappTemplateSeeder extends Seeder
{
    public function run()
    {
        WhatsappTemplate::updateOrCreate(
            ['name' => 'herbalife_promo'],
            [
                'category' => 'MARKETING',
                'content' => "¡Hola {{1}}! 🌿\n\n*¡Transforma tu vida con Herbalife!* 💪\n\n{{2}}\n\n*Beneficios:*\n{{3}}\n\n*¡Oferta Especial!* 🎉\n{{4}}\n\n{{5}}\n\n¿Te gustaría recibir más información? Responde 'SI' y te enviaré todos los detalles. 😊",
                'language' => 'es',
                'status' => 'active',
                'variables' => [
                    'name',
                    'product_description',
                    'benefits',
                    'promo_details',
                    'call_to_action'
                ]
            ]
        );
    }
}
