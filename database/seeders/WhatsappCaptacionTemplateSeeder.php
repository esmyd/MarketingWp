<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WhatsappTemplate;

class WhatsappCaptacionTemplateSeeder extends Seeder
{
    public function run()
    {
        WhatsappTemplate::updateOrCreate(
            ['name' => 'plantilla_de_captacion'],
            [
                'category' => 'MARKETING',
                'content' => "¡Hola {{1}}!!\n\nSomos el equipo de Siglo Tecnológico, especialistas en desarrollo de software personalizado para empresas como la tuya.\n\n¿Sabías que podemos ayudarte a:\n• Automatizar procesos\n• Desarrollar apps móviles o web\n• Integrar sistemas existentes\n• Mejorar tu infraestructura tecnológica?\n\n👉 Escríbenos 'Interesado' para recibir información detallada o agenda una consulta gratuita hoy mismo.\n\n¡Innovamos tu tecnología para llevar tu negocio al futuro!",
                'language' => 'es',
                'status' => 'active',
                'variables' => ['customer_name']
            ]
        );
    }
}
