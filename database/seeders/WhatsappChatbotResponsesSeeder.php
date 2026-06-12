<?php

namespace Database\Seeders;

use App\Models\WhatsappChatbotResponse;
use Illuminate\Database\Seeder;

class WhatsappChatbotResponsesSeeder extends Seeder
{
    public function run(): void
    {
        $responses = [
            // ─── Saludos ─────────────────────────────────────────────────────
            [
                'keyword'   => 'hola',
                'response'  => "¡Hola! 👋 Bienvenido a *Siglo Tecnológico S.A.* 💻\n\nSoy tu asistente virtual especializado en soluciones tecnológicas para empresas.\n\nPuedes preguntarme sobre:\n• 🖥️ Software Empresarial (CRM, ERP, Facturación)\n• 🌐 Desarrollo Web y Apps\n• 🤖 Chatbots con Inteligencia Artificial\n• 🛍️ Tiendas E-commerce\n• ⚡ Automatización Empresarial\n• 💰 Precios y Planes\n\n¿En qué puedo ayudarte?",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 1,
            ],
            [
                'keyword'   => 'hi',
                'response'  => "¡Hola! 👋 Bienvenido a *Siglo Tecnológico S.A.* 💻\n\nSoy tu asistente virtual. Escribe *hola* para ver el menú completo o dime directamente en qué puedo ayudarte.",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 2,
            ],
            [
                'keyword'   => 'buenas',
                'response'  => "¡Buenas! 😊 Bienvenido a *Siglo Tecnológico S.A.* 💻\n\nEstoy aquí para ayudarte con nuestras soluciones tecnológicas. ¿En qué te puedo asistir?",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 3,
            ],

            // ─── Información de la empresa ────────────────────────────────────
            [
                'keyword'   => 'horarios',
                'response'  => "🕒 *Horarios de Atención — Siglo Tecnológico S.A.*\n\n📅 *Lunes a Viernes*\n• 9:00 AM – 1:00 PM\n• 2:00 PM – 6:00 PM\n\n📅 *Sábados*\n• 9:00 AM – 1:00 PM\n\n🚫 Domingos y feriados: Cerrado\n\n💬 *Soporte por WhatsApp*: Disponible 24/7 (respuesta automatizada)\n📞 Atención humana en horario laboral.\n\n¿Hay algo más en que pueda ayudarte?",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 10,
            ],
            [
                'keyword'   => 'contacto',
                'response'  => "📞 *Información de Contacto*\n\n*Siglo Tecnológico S.A.*\n\n• 📱 WhatsApp: +593 98 849 2339\n• 📞 Teléfono: +593 98 849 2339\n• 📧 Email: info@siglotecnologico.com\n• 🌐 Web: www.siglotecnologico.com\n\n📍 *Dirección*:\nAv. de las Américas, Guayaquil, Ecuador\n\n⏰ Atención: Lun-Vie 9:00-18:00 | Sáb 9:00-13:00",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 11,
            ],
            [
                'keyword'   => 'envios',
                'response'  => "🚀 *Tiempos de Entrega de Proyectos*\n\n*Siglo Tecnológico S.A.*\n\n| Servicio | Tiempo estimado |\n|---|---|\n| 🌐 Landing Page | 5-7 días hábiles |\n| 🌐 Página Corporativa | 10-15 días hábiles |\n| 🤖 Chatbot Básico | 2-3 días hábiles |\n| 🤖 Chatbot Profesional | 5-7 días hábiles |\n| 🛍️ Tienda E-commerce | 15-25 días hábiles |\n| 📱 App Móvil | 30-60 días hábiles |\n\n⚡ *Servicio Express*: disponible con recargo del 30%.\n\nTodos los proyectos incluyen soporte post-entrega de 30 días.",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 12,
            ],
            [
                'keyword'   => 'pagos',
                'response'  => "💳 *Métodos de Pago — Siglo Tecnológico S.A.*\n\nAceptamos:\n✅ Transferencia bancaria (Ecuador)\n✅ Tarjeta de crédito/débito (Visa, Mastercard)\n✅ PayPal\n✅ Efectivo en oficina\n✅ Depósito bancario\n\n🏦 *Banco Pichincha*\nCta. Corriente: 2200000001\nRUC: 0990000001001\n\n📋 *Condiciones*:\n• Proyectos bajo $500: 100% al inicio\n• Proyectos $500-$2000: 50% inicio / 50% entrega\n• Proyectos +$2000: 40% inicio / 30% avance / 30% entrega\n\nFacturación electrónica disponible.",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 13,
            ],
            [
                'keyword'   => 'asesoria',
                'response'  => 'Compartiendo el contacto de nuestro asesor tecnológico...',
                'type'      => 'contacts',
                'is_active' => true,
                'show_menu' => true,
                'order'     => 14,
                'contacts'  => "Asesor Tecnológico|Asesor|Tecnológico|593988492339|info@siglotecnologico.com|Siglo Tecnológico S.A.|Asesoría Tecnológica|Especialista en Soluciones Tecnológicas",
            ],
            [
                'keyword'   => 'soporte',
                'response'  => 'Compartiendo el contacto de nuestro equipo de soporte técnico...',
                'type'      => 'contacts',
                'is_active' => true,
                'show_menu' => true,
                'order'     => 15,
                'contacts'  => "Soporte Técnico|Soporte|Técnico|593988492339|soporte@siglotecnologico.com|Siglo Tecnológico S.A.|Soporte Técnico|Especialista en Soporte y Mantenimiento",
            ],
            [
                'keyword'   => 'ventas',
                'response'  => 'Compartiendo el contacto de nuestro equipo de ventas...',
                'type'      => 'contacts',
                'is_active' => true,
                'show_menu' => true,
                'order'     => 16,
                'contacts'  => "Ventas|Ejecutivo|Ventas|593988492339|ventas@siglotecnologico.com|Siglo Tecnológico S.A.|Ventas|Ejecutivo de Ventas Corporativas",
            ],
            [
                'keyword'   => 'redes',
                'response'  => "📱 *Redes Sociales — Siglo Tecnológico S.A.*\n\nEncuéntranos en:\n\n• 📸 Instagram: @siglotecnologico\n• 👍 Facebook: /siglotecnologico\n• 💼 LinkedIn: /company/siglotecnologico\n• 🐦 Twitter/X: @siglotec\n• 🎥 YouTube: Siglo Tecnológico\n\n🌐 Web: www.siglotecnologico.com\n\n¡Síguenos para tips tecnológicos y ofertas exclusivas! 🚀",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 17,
            ],

            // ─── Servicios ───────────────────────────────────────────────────
            [
                'keyword'   => 'software',
                'response'  => "🖥️ *Software Empresarial — Siglo Tecnológico*\n\nTransforma tu empresa con nuestras soluciones:\n\n| Solución | Desde |\n|---|---|\n| CRM Básico (5 usuarios) | $49/mes |\n| CRM Empresarial (ilimitado) | $149/mes |\n| Sistema ERP | $299/mes |\n| Facturación Electrónica | $79/mes |\n| Suite Completa | $399/mes |\n\n✅ Soporte incluido\n✅ Actualizaciones gratuitas\n✅ Capacitación de 2h incluida\n\n¿Te gustaría una demo gratuita?",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 20,
            ],
            [
                'keyword'   => 'chatbot',
                'response'  => "🤖 *Chatbots con IA — Siglo Tecnológico*\n\nAutomatiza tu atención al cliente 24/7:\n\n| Plan | Precio | Mensajes |\n|---|---|---|\n| Básico | $25/mes | 1.000/mes |\n| Profesional | $80/mes | 5.000/mes |\n| Empresarial | $150/mes | Ilimitados |\n| Con GPT-4 | $249/mes | Ilimitados |\n\n✅ Catálogo de productos\n✅ Carrito de compras\n✅ Derivación a agente humano\n✅ Reportes y analytics\n\n¡Prueba gratis 7 días! 🎉",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 21,
            ],
            [
                'keyword'   => 'web',
                'response'  => "🌐 *Desarrollo Web — Siglo Tecnológico*\n\nPresencia digital profesional:\n\n• 🎯 Landing Page: desde $199\n• 🏢 Página Corporativa: desde $449\n• 🛍️ Tienda Online: desde $549\n• 💻 App Web a medida: desde $1.999\n• 📱 App Móvil (iOS+Android): desde $2.499\n\n✅ Diseño responsivo\n✅ SEO incluido\n✅ Dominio y hosting 1 año\n✅ Panel de administración\n✅ Soporte 30 días\n\n¿Agendamos una consulta gratuita?",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 22,
            ],
            [
                'keyword'   => 'desarrollo',
                'response'  => "💻 *Servicios de Desarrollo — Siglo Tecnológico*\n\nCreamos soluciones tecnológicas a medida:\n\n🌐 *Desarrollo Web*\n• Páginas corporativas, landing pages, e-commerce\n• Aplicaciones web personalizadas\n\n📱 *Apps Móviles*\n• iOS, Android o multiplataforma (Flutter)\n• Delivery, reservas, catálogos\n\n🤖 *Inteligencia Artificial*\n• Chatbots avanzados con GPT\n• Automatización de procesos\n\n¿Agendamos una consulta técnica gratuita?",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 23,
            ],
            [
                'keyword'   => 'ecommerce',
                'response'  => "🛍️ *E-commerce — Siglo Tecnológico*\n\nVende más con tu propia tienda online:\n\n• *Tienda Online Básica* — desde $549\n  ✅ Catálogo, carrito, pagos, admin\n\n• *Marketplace Multi-vendedor* — desde $1.299\n  ✅ Múltiples vendedores, comisiones automáticas\n\n• *E-commerce + App Móvil* — desde $2.999\n  ✅ Web + App iOS + App Android\n\n¿Tienes un proyecto en mente? Cuéntame más y te preparo una propuesta personalizada.",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 24,
            ],
            [
                'keyword'   => 'automatizacion',
                'response'  => "⚡ *Automatización Empresarial — Siglo Tecnológico*\n\nOptimiza tu negocio con automatización inteligente:\n\n• 📧 Marketing Automation: $449/mes\n  Email + redes + CRM integrado\n\n• 📱 Automatización WhatsApp: $199/mes\n  Campañas masivas + analytics\n\n• 🔄 Automatización Integral: $499/mes\n  Marketing + WhatsApp + procesos\n\n✅ Reduce costos operativos hasta 60%\n✅ Atención 24/7 sin contratar personal\n✅ ROI comprobado en 3 meses",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 25,
            ],

            // ─── Precios y consultas ──────────────────────────────────────────
            [
                'keyword'   => 'precios',
                'response'  => "💰 *Tarifas — Siglo Tecnológico S.A.*\n\nNuestros precios varían según el servicio:\n\n🤖 Chatbots: desde $25/mes\n🖥️ Software: desde $49/mes\n🌐 Web: desde $199 (pago único)\n🛍️ E-commerce: desde $549 (pago único)\n📱 Apps: desde $799 (pago único)\n⚡ Automatización: desde $199/mes\n\nTodos los precios en USD. Consulta nuestro catálogo completo seleccionando 💻 *Soluciones* en el menú.",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 30,
            ],

            // ─── Otros ───────────────────────────────────────────────────────
            [
                'keyword'   => 'gracias',
                'response'  => "¡Gracias por contactarnos! 😊🚀\n\nEn *Siglo Tecnológico S.A.* estamos comprometidos con el éxito digital de tu empresa.\n\nRecuerda que puedes contactarnos en cualquier momento.\n\n¡Que tengas un excelente día! 💼",
                'type'      => 'text',
                'show_menu' => false,
                'is_active' => true,
                'order'     => 50,
            ],
            [
                'keyword'   => 'faq',
                'response'  => "❓ *Preguntas Frecuentes — Siglo Tecnológico*\n\n*1. ¿Qué garantía tienen sus servicios?*\nTodos nuestros desarrollos incluyen garantía de 3 meses por errores de código.\n\n*2. ¿Ofrecen mantenimiento?*\nSí, tenemos planes de mantenimiento mensual desde $30/mes.\n\n*3. ¿Los softwares SaaS tienen contrato de permanencia?*\nNo. Puedes cancelar en cualquier momento sin penalidad.\n\n*4. ¿Dan capacitación?*\nSí, todos los proyectos incluyen capacitación de 2 horas.\n\n*5. ¿Trabajan con empresas fuera de Ecuador?*\nSí, atendemos clientes en toda Latinoamérica.\n\n*6. ¿Tienen facturación electrónica?*\nSí, emitimos facturas electrónicas.\n\n¿Tienes otra pregunta? Escríbenos o habla con un asesor.",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 40,
            ],
            [
                'keyword'   => 'demo',
                'response'  => "🎯 *Solicitar Demo Gratuita*\n\n¡Genial! Me alegra que quieras conocer nuestras soluciones en detalle.\n\nPara agendar tu *demo gratuita de 30 minutos* necesito:\n\n📝 Tu nombre completo\n🏢 Nombre de tu empresa\n📱 Tu número de teléfono\n⏰ Tu disponibilidad horaria\n💼 Servicio de interés\n\nTambién puedes escribirnos directamente al:\n📧 info@siglotecnologico.com\n📞 +593 98 849 2339",
                'type'      => 'text',
                'show_menu' => false,
                'is_active' => true,
                'order'     => 45,
            ],
            [
                'keyword'   => 'menu',
                'response'  => "📋 *Menú Principal — Siglo Tecnológico S.A.*",
                'type'      => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order'     => 60,
            ],
        ];

        foreach ($responses as $response) {
            WhatsappChatbotResponse::updateOrCreate(
                ['keyword' => $response['keyword']],
                $response
            );
        }
    }
}
