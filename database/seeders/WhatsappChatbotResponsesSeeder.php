<?php

namespace Database\Seeders;

use App\Models\WhatsappChatbotResponse;
use Illuminate\Database\Seeder;

class WhatsappChatbotResponsesSeeder extends Seeder
{
    public function run()
    {
        $responses = [
            [
                'keyword' => 'hola',
                'response' => "¡Hola! Bienvenido a Siglo Tecnológico. 💻\n\nSoy tu asistente virtual y estoy aquí para ayudarte a encontrar las mejores soluciones tecnológicas para tu negocio.\n\nPuedes preguntarme por:\n• Software Empresarial 🖥️\n• Desarrollo Web 🌐\n• E-commerce 🛍️\n• Chatbots 🤖\n• Automatización ⚡\n• Precios y Planes 💰\n• Soporte Técnico 🛠️",
                'type' => 'text',
                'show_menu' => true,
                'order' => 1
            ],
            [
                'keyword' => 'productos',
                'response' => "Tenemos una amplia gama de productos para tu salud y bienestar:\n\n1️⃣ Suplementos nutricionales\n2️⃣ Batidos y proteínas\n3️⃣ Vitaminas y minerales\n4️⃣ Productos para el control de peso\n5️⃣ Cuidado personal\n\n¿Te gustaría conocer más sobre algún producto específico?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 2
            ],
            [
                'keyword' => 'productos_tech',
                'response' => "Tenemos una amplia gama de soluciones tecnológicas para tu negocio:\n\n1️⃣ Software Empresarial\n2️⃣ Desarrollo Web\n3️⃣ Aplicaciones Móviles\n4️⃣ E-commerce\n5️⃣ Chatbots y Automatización\n\n¿Te gustaría conocer más sobre alguna solución específica?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order' => 2.1
            ],
            [
                'keyword' => 'precios',
                'response' => "Nuestros precios varían según el producto y las promociones vigentes. Para darte la mejor asesoría, por favor indícame qué producto te interesa:\n\n• Batidos\n• Vitaminas\n• Suplementos\n• Control de peso\n\nTambién tenemos planes de membresía con descuentos especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 3
            ],
            [
                'keyword' => 'precios_tech',
                'response' => "Nuestros precios varían según el servicio y las necesidades de tu proyecto. Para darte la mejor asesoría, por favor indícame qué servicio te interesa:\n\n• Desarrollo Web\n• Aplicaciones Móviles\n• Software Empresarial\n• E-commerce\n• Chatbots\n\nTambién tenemos planes empresariales con beneficios especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order' => 3.1
            ],
            [
                'keyword' => 'horarios',
                'response' => "🕒 *Horarios de Atención*\n\n" .
                    "Lunes a Viernes:\n" .
                    "• 9:00 AM - 1:00 PM\n" .
                    "• 2:00 PM - 6:00 PM\n\n" .
                    "Sábados:\n" .
                    "• 9:00 AM - 1:00 PM\n\n" .
                    "Domingos: Cerrado\n\n" .
                    "📍 *Ubicación*:\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, País",
                'type' => 'text',
                'show_menu' => true,
                'order' => 16
            ],
            [
                'keyword' => 'contacto',
                'response' => "📞 *Información de Contacto*\n\n" .
                    "• WhatsApp: +593988492339\n" .
                    "• Teléfono: +593988492339\n" .
                    "• Email: info@siglotecnologico.com\n\n" .
                    "Horario de atención telefónica:\n" .
                    "Lunes a Viernes: 9:00 AM - 6:00 PM",
                'type' => 'text',
                'show_menu' => true,
                'order' => 17
            ],
            [
                'keyword' => 'envios',
                'response' => "🚚 *Información de Envíos*\n\n" .
                    "• Envío estándar: 2-3 días hábiles\n" .
                    "• Envío express: 24 horas\n" .
                    "• Retiro en tienda: Gratis\n\n" .
                    "Zonas de cobertura:\n" .
                    "• Ciudad principal y alrededores\n" .
                    "• Envíos a nivel nacional\n\n" .
                    "Costo de envío:\n" .
                    "• Gratis en compras mayores a $50\n" .
                    "• $5 en compras menores",
                'type' => 'text',
                'show_menu' => true,
                'order' => 18
            ],
            [
                'keyword' => 'pagos',
                'response' => "💳 *Métodos de Pago*\n\n" .
                    "Aceptamos:\n" .
                    "• Tarjetas de crédito/débito\n" .
                    "• Transferencia bancaria\n" .
                    "• Efectivo\n" .
                    "• PayPal\n\n" .
                    "Promociones especiales:\n" .
                    "• 3 meses sin intereses\n" .
                    "• 10% de descuento en efectivo",
                'type' => 'text',
                'show_menu' => true,
                'order' => 19
            ],
            [
                'keyword' => 'asesoria',
                'response' => 'Compartiendo el contacto de nuestro asesor...',
                'type' => 'contacts',
                'is_active' => true,
                'show_menu' => true,
                'order' => 5,
                'contacts' => "Ing. Gregorio Osorio|Gregorio|Osorio|593988492339|info@siglotecnologico.com|Siglo Tecnologico|Asesoría Tecnologica|Asesor de Tecnologias"
            ],
            [
                'keyword' => 'soporte',
                'response' => 'Compartiendo el contacto de nuestro equipo de soporte...',
                'type' => 'contacts',
                'is_active' => true,
                'show_menu' => true,
                'order' => 6,
                'contacts' => "Carlos Rodríguez|Carlos|Rodríguez|593988492339|soporte@siglotecnologico.com|Siglo Tecnologico|Soporte Tecnologico|Especialista en Soporte"
            ],
            [
                'keyword' => 'ventas',
                'response' => 'Compartiendo el contacto de nuestro equipo de ventas...',
                'type' => 'contacts',
                'is_active' => true,
                'show_menu' => true,
                'order' => 7,
                'contacts' => "María González|María|González|593988492339|ventas@empresa.com|Salud Natural|Ventas|Asesora de Ventas"
            ],
            [
                'keyword' => 'redes',
                'response' => "📱 *Redes Sociales*\n\n" .
                    "Síguenos en:\n" .
                    "• Instagram: @empresa\n" .
                    "• Facebook: /empresa\n" .
                    "• Twitter: @empresa\n" .
                    "• YouTube: /empresa\n\n" .
                    "¡Mantente al día con nuestras novedades y promociones!",
                'type' => 'text',
                'show_menu' => true,
                'order' => 21
            ],
            [
                'keyword' => 'membresia',
                'response' => "Nuestra membresía VIP incluye:\n\n✨ 15% de descuento en todos los productos\n✨ Envío gratis en todas tus compras\n✨ Asesoría nutricional personalizada\n✨ Acceso a promociones exclusivas\n✨ Regalos mensuales\n\nPara registrarte, envía:\n📝 Tu nombre completo\n📱 Tu número de teléfono\n📧 Tu correo electrónico",
                'type' => 'text',
                'show_menu' => true,
                'order' => 11
            ],
            [
                'keyword' => 'gracias',
                'response' => "¡Gracias por contactarnos! 😊\n\nRecuerda que estamos aquí para ayudarte en tu camino hacia una vida más saludable. Si necesitas algo más, no dudes en preguntar.\n\n¡Que tengas un excelente día! 🌟",
                'type' => 'text',
                'show_menu' => true,
                'order' => 12
            ],
            [
                'keyword' => 'promociones',
                'response' => "🎁 *Promociones Actuales*\n\n" .
                    "• 20% OFF en tu primera compra\n" .
                    "• 2x1 en batidos seleccionados\n" .
                    "• Envío gratis en compras mayores a $50\n" .
                    "• Kit de inicio con 15% de descuento\n\n" .
                    "¡Aprovecha estas ofertas por tiempo limitado! ⏰",
                'type' => 'text',
                'show_menu' => true,
                'order' => 14
            ],
            [
                'keyword' => 'faq',
                'response' => "❓ *Preguntas Frecuentes*\n\n" .
                    "1. ¿Tienen garantía?\n" .
                    "Sí, todos nuestros productos tienen garantía de 30 días.\n\n" .
                    "2. ¿Cómo puedo devolver un producto?\n" .
                    "Tienes 15 días para devoluciones en su empaque original.\n\n" .
                    "3. ¿Cuánto tarda el envío?\n" .
                    "Entre 24-48 horas en ciudad, 2-4 días a nivel nacional.\n\n" .
                    "4. ¿Aceptan tarjetas?\n" .
                    "Sí, aceptamos todas las tarjetas principales.\n\n" .
                    "5. ¿Tienen tienda física?\n" .
                    "Sí, contamos con varias sucursales. Escribe 'sucursales' para ver las ubicaciones.\n\n" .
                    "6. ¿Cómo puedo hacer un pedido?\n" .
                    "Puedes hacerlo por WhatsApp, en tienda o en nuestra página web.\n\n" .
                    "7. ¿Tienen envío gratis?\n" .
                    "Sí, en compras mayores a $50.\n\n" .
                    "8. ¿Los productos son originales?\n" .
                    "Sí, todos nuestros productos son 100% originales y certificados.\n\n" .
                    "9. ¿Puedo pagar contra entrega?\n" .
                    "Sí, ofrecemos pago contra entrega en envíos locales.\n\n" .
                    "10. ¿Tienen asesoría nutricional?\n" .
                    "Sí, contamos con asesores certificados. Escribe 'asesoria' para más información.\n\n" .
                    "11. ¿Los productos tienen efectos secundarios?\n" .
                    "Todos nuestros productos son seguros, pero recomendamos consultar con un profesional de la salud.\n\n" .
                    "12. ¿Tienen programa de fidelidad?\n" .
                    "Sí, tenemos un programa de membresía VIP. Escribe 'membresia' para conocer los beneficios.\n\n" .
                    "13. ¿Puedo combinar promociones?\n" .
                    "Las promociones no son acumulables con otros descuentos.\n\n" .
                    "14. ¿Tienen servicio al cliente 24/7?\n" .
                    "Nuestro horario de atención es de 9:00 AM a 6:00 PM, de lunes a sábado.\n\n" .
                    "15. ¿Cómo puedo rastrear mi pedido?\n" .
                    "Te enviaremos un número de seguimiento por WhatsApp cuando tu pedido sea enviado.",
                'type' => 'text',
                'show_menu' => true,
                'order' => 15
            ],
            [
                'keyword' => 'sucursales',
                'response' => "🏪 *Nuestras Sucursales*\n\n" .
                    "📍 *Sucursal Principal*\n" .
                    "Av. Principal #123\n" .
                    "Horario: L-V 9:00-19:00\n\n" .
                    "📍 *Sucursal Norte*\n" .
                    "Centro Comercial Norte #456\n" .
                    "Horario: L-S 10:00-20:00\n\n" .
                    "📍 *Sucursal Sur*\n" .
                    "Plaza Sur #789\n" .
                    "Horario: L-V 9:00-18:00",
                'type' => 'text',
                'show_menu' => true,
                'order' => 16
            ],
            [
                'keyword' => 'devoluciones',
                'response' => "🔄 *Política de Devoluciones*\n\n" .
                    "• 15 días para devoluciones\n" .
                    "• Producto en empaque original\n" .
                    "• Recibo de compra\n" .
                    "• Sin uso del producto\n\n" .
                    "Para iniciar una devolución, envía:\n" .
                    "📝 Número de orden\n" .
                    "📸 Foto del producto\n" .
                    "📄 Motivo de devolución",
                'type' => 'text',
                'show_menu' => true,
                'order' => 17
            ],
            [
                'keyword' => 'garantia',
                'response' => "✅ *Garantía de Productos*\n\n" .
                    "• 30 días de garantía\n" .
                    "• Cobertura por defectos de fábrica\n" .
                    "• Reemplazo inmediato\n" .
                    "• Sin costo adicional\n\n" .
                    "Para reclamar garantía, envía:\n" .
                    "📝 Número de orden\n" .
                    "📸 Foto del defecto\n" .
                    "📄 Descripción del problema",
                'type' => 'text',
                'show_menu' => true,
                'order' => 18
            ],
            [
                'keyword' => 'nutricion',
                'response' => "🥗 *Asesoría Nutricional*\n\n" .
                    "Nuestros planes incluyen:\n" .
                    "• Evaluación inicial\n" .
                    "• Plan personalizado\n" .
                    "• Seguimiento semanal\n" .
                    "• Recetas saludables\n\n" .
                    "Para agendar tu consulta:\n" .
                    "📅 Envía tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 19
            ],
            [
                'keyword' => 'desarrollo',
                'response' => "💻 *Servicios de Desarrollo*\n\n" .
                    "Ofrecemos:\n" .
                    "• Desarrollo Web Personalizado\n" .
                    "• Aplicaciones Móviles\n" .
                    "• Software Empresarial\n" .
                    "• E-commerce\n" .
                    "• Chatbots y Automatización\n\n" .
                    "Para agendar una consulta:\n" .
                    "📅 Envía tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order' => 19.1
            ],
            [
                'keyword' => 'delivery',
                'response' => "🚚 *Servicio de Delivery*\n\n" .
                    "• Entrega en 24-48 horas\n" .
                    "• Sin costo en compras +$50\n" .
                    "• Rastreo en tiempo real\n" .
                    "• Pago contra entrega\n\n" .
                    "Para pedir delivery:\n" .
                    "1. Selecciona tus productos\n" .
                    "2. Proporciona dirección\n" .
                    "3. Elige método de pago",
                'type' => 'text',
                'show_menu' => true,
                'order' => 20
            ],
            [
                'keyword' => 'ayuda',
                'response' => "🆘 *¿Necesitas ayuda?*\n\n" .
                    "Estoy aquí para ayudarte con:\n" .
                    "• Información de productos\n" .
                    "• Realizar pedidos\n" .
                    "• Consultas generales\n" .
                    "• Soporte técnico\n\n" .
                    "Escribe tu pregunta o selecciona una opción del menú.",
                'type' => 'text',
                'show_menu' => true,
                'order' => 21
            ],
            [
                'keyword' => 'menu',
                'response' => "📋 *Menú Principal*\n\n" .
                    "1. 🛍️ Productos\n" .
                    "2. 💰 Precios\n" .
                    "3. 🕒 Horarios\n" .
                    "4. 🎁 Promociones\n" .
                    "5. 👨‍💼 Asesoría\n" .
                    "6. 📱 Redes Sociales\n" .
                    "7. ❓ FAQ\n" .
                    "8. 🏪 Sucursales\n\n" .
                    "Selecciona un número o escribe la opción deseada.",
                'type' => 'text',
                'show_menu' => true,
                'order' => 22
            ],
            [
                'keyword' => 'hi',
                'response' => "¡Hola! Bienvenido a Siglo Tecnológico. 💻\n\nSoy tu asistente virtual y estoy aquí para ayudarte a encontrar las mejores soluciones tecnológicas para tu negocio.\n\nPuedes preguntarme por:\n• Software Empresarial 🖥️\n• Desarrollo Web 🌐\n• E-commerce 🛍️\n• Chatbots 🤖\n• Automatización ⚡\n• Precios y Planes 💰\n• Soporte Técnico 🛠️",
                'type' => 'text',
                'show_menu' => true,
                'order' => 23
            ],
            [
                'keyword' => 'buenas',
                'response' => "¡Hola! Bienvenido a Siglo Tecnológico. 💻\n\nSoy tu asistente virtual y estoy aquí para ayudarte a encontrar las mejores soluciones tecnológicas para tu negocio.\n\nPuedes preguntarme por:\n• Software Empresarial 🖥️\n• Desarrollo Web 🌐\n• E-commerce 🛍️\n• Chatbots 🤖\n• Automatización ⚡\n• Precios y Planes 💰\n• Soporte Técnico 🛠️",
                'type' => 'text',
                'show_menu' => true,
                'order' => 24
            ],
            [
                'keyword' => 'buen dia',
                'response' => "¡Hola! Bienvenido a Siglo Tecnológico. 💻\n\nSoy tu asistente virtual y estoy aquí para ayudarte a encontrar las mejores soluciones tecnológicas para tu negocio.\n\nPuedes preguntarme por:\n• Software Empresarial ��️\n• Desarrollo Web 🌐\n• E-commerce 🛍️\n• Chatbots 🤖\n• Automatización ⚡\n• Precios y Planes 💰\n• Soporte Técnico 🛠️",
                'type' => 'text',
                'show_menu' => true,
                'order' => 25
            ],
            [
                'keyword' => 'prod',
                'response' => "Tenemos una amplia gama de productos para tu salud y bienestar:\n\n1️⃣ Suplementos nutricionales\n2️⃣ Batidos y proteínas\n3️⃣ Vitaminas y minerales\n4️⃣ Productos para el control de peso\n5️⃣ Cuidado personal\n\n¿Te gustaría conocer más sobre algún producto específico?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 26
            ],
            [
                'keyword' => 'producto',
                'response' => "Tenemos una amplia gama de productos para tu salud y bienestar:\n\n1️⃣ Suplementos nutricionales\n2️⃣ Batidos y proteínas\n3️⃣ Vitaminas y minerales\n4️⃣ Productos para el control de peso\n5️⃣ Cuidado personal\n\n¿Te gustaría conocer más sobre algún producto específico?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 27
            ],
            [
                'keyword' => 'precio',
                'response' => "Nuestros precios varían según el producto y las promociones vigentes. Para darte la mejor asesoría, por favor indícame qué producto te interesa:\n\n• Batidos\n• Vitaminas\n• Suplementos\n• Control de peso\n\nTambién tenemos planes de membresía con descuentos especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 28
            ],
            [
                'keyword' => 'costo',
                'response' => "Nuestros precios varían según el producto y las promociones vigentes. Para darte la mejor asesoría, por favor indícame qué producto te interesa:\n\n• Batidos\n• Vitaminas\n• Suplementos\n• Control de peso\n\nTambién tenemos planes de membresía con descuentos especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 29
            ],
            [
                'keyword' => 'valor',
                'response' => "Nuestros precios varían según el producto y las promociones vigentes. Para darte la mejor asesoría, por favor indícame qué producto te interesa:\n\n• Batidos\n• Vitaminas\n• Suplementos\n• Control de peso\n\nTambién tenemos planes de membresía con descuentos especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 30
            ],
            [
                'keyword' => 'hora',
                'response' => "🕒 *Horarios de Atención*\n\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "📍 *Ubicación*\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 31
            ],
            [
                'keyword' => 'atencion',
                'response' => "🕒 *Horarios de Atención*\n\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "📍 *Ubicación*\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 32
            ],
            [
                'keyword' => 'contactar',
                'response' => "📞 *Información de Contacto*\n\n" .
                    "Teléfono: +593988492339\n" .
                    "WhatsApp: +1 +593988492339\n" .
                    "Email: info@siglotecnologico.com\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 33
            ],
            [
                'keyword' => 'telefono',
                'response' => "📞 *Información de Contacto*\n\n" .
                    "Teléfono: +593988492339\n" .
                    "WhatsApp: +1 +593988492339\n" .
                    "Email: info@siglotecnologico.com\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 34
            ],
            [
                'keyword' => 'envio',
                'response' => "🚚 *Información de Envíos*\n\n" .
                    "• Envío local: 24-48 horas\n" .
                    "• Envío nacional: 2-4 días hábiles\n" .
                    "• Envío internacional: 5-7 días hábiles\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 35
            ],
            [
                'keyword' => 'entrega',
                'response' => "🚚 *Información de Envíos*\n\n" .
                    "• Envío local: 24-48 horas\n" .
                    "• Envío nacional: 2-4 días hábiles\n" .
                    "• Envío internacional: 5-7 días hábiles\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 36
            ],
            [
                'keyword' => 'pago',
                'response' => "💳 *Métodos de Pago*\n\n" .
                    "• Transferencia bancaria\n" .
                    "• Pago en efectivo\n" .
                    "• Tarjeta de crédito/débito\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 37
            ],
            [
                'keyword' => 'tarjeta',
                'response' => "💳 *Métodos de Pago*\n\n" .
                    "• Transferencia bancaria\n" .
                    "• Pago en efectivo\n" .
                    "• Tarjeta de crédito/débito\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 38
            ],
            [
                'keyword' => 'asesor',
                'response' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                    "Nuestros asesores están certificados para ayudarte a:\n\n" .
                    "• Crear un plan personalizado\n" .
                    "• Seleccionar los productos adecuados\n" .
                    "• Seguimiento de tu progreso\n\n" .
                    "Para agendar una cita, por favor envía:\n" .
                    "📅 Tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 39
            ],
            [
                'keyword' => 'consulta',
                'response' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                    "Nuestros asesores están certificados para ayudarte a:\n\n" .
                    "• Crear un plan personalizado\n" .
                    "• Seleccionar los productos adecuados\n" .
                    "• Seguimiento de tu progreso\n\n" .
                    "Para agendar una cita, por favor envía:\n" .
                    "📅 Tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 40
            ],
            [
                'keyword' => 'ayudar',
                'response' => "🆘 *¿Necesitas ayuda?*\n\n" .
                    "Estoy aquí para ayudarte con:\n" .
                    "• Información de productos\n" .
                    "• Realizar pedidos\n" .
                    "• Consultas generales\n" .
                    "• Soporte técnico\n\n" .
                    "Escribe tu pregunta o selecciona una opción del menú.",
                'type' => 'text',
                'show_menu' => true,
                'order' => 46
            ],
            [
                'keyword' => 'agente',
                'response' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                    "Nuestros asesores están certificados para ayudarte a:\n\n" .
                    "• Crear un plan personalizado\n" .
                    "• Seleccionar los productos adecuados\n" .
                    "• Seguimiento de tu progreso\n\n" .
                    "Para agendar una cita, por favor envía:\n" .
                    "📅 Tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 47
            ],
            [
                'keyword' => 'persona',
                'response' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                    "Nuestros asesores están certificados para ayudarte a:\n\n" .
                    "• Crear un plan personalizado\n" .
                    "• Seleccionar los productos adecuados\n" .
                    "• Seguimiento de tu progreso\n\n" .
                    "Para agendar una cita, por favor envía:\n" .
                    "📅 Tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 48
            ],
            [
                'keyword' => 'humano',
                'response' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                    "Nuestros asesores están certificados para ayudarte a:\n\n" .
                    "• Crear un plan personalizado\n" .
                    "• Seleccionar los productos adecuados\n" .
                    "• Seguimiento de tu progreso\n\n" .
                    "Para agendar una cita, por favor envía:\n" .
                    "📅 Tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 49
            ],
            [
                'keyword' => 'especialista',
                'response' => "👨‍💼 *Asesoría Personalizada*\n\n" .
                    "Nuestros asesores están certificados para ayudarte a:\n\n" .
                    "• Crear un plan personalizado\n" .
                    "• Seleccionar los productos adecuados\n" .
                    "• Seguimiento de tu progreso\n\n" .
                    "Para agendar una cita, por favor envía:\n" .
                    "📅 Tu nombre\n" .
                    "📱 Tu número de teléfono\n" .
                    "⏰ Horario preferido\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 50
            ],
            [
                'keyword' => 'articulos',
                'response' => "Tenemos una amplia gama de productos para tu salud y bienestar:\n\n1️⃣ Suplementos nutricionales\n2️⃣ Batidos y proteínas\n3️⃣ Vitaminas y minerales\n4️⃣ Productos para el control de peso\n5️⃣ Cuidado personal\n\n¿Te gustaría conocer más sobre algún producto específico?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 51
            ],
            [
                'keyword' => 'articulos_tech',
                'response' => "Tenemos una amplia gama de soluciones tecnológicas para tu negocio:\n\n1️⃣ Software Empresarial\n2️⃣ Desarrollo Web\n3️⃣ Aplicaciones Móviles\n4️⃣ E-commerce\n5️⃣ Chatbots y Automatización\n\n¿Te gustaría conocer más sobre alguna solución específica?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order' => 51.1
            ],
            [
                'keyword' => 'items',
                'response' => "Tenemos una amplia gama de productos para tu salud y bienestar:\n\n1️⃣ Suplementos nutricionales\n2️⃣ Batidos y proteínas\n3️⃣ Vitaminas y minerales\n4️⃣ Productos para el control de peso\n5️⃣ Cuidado personal\n\n¿Te gustaría conocer más sobre algún producto específico?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 52
            ],
            [
                'keyword' => 'items_tech',
                'response' => "Tenemos una amplia gama de soluciones tecnológicas para tu negocio:\n\n1️⃣ Software Empresarial\n2️⃣ Desarrollo Web\n3️⃣ Aplicaciones Móviles\n4️⃣ E-commerce\n5️⃣ Chatbots y Automatización\n\n¿Te gustaría conocer más sobre alguna solución específica?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => true,
                'order' => 52.1
            ],
            [
                'keyword' => 'cuanto',
                'response' => "Nuestros precios varían según el producto y las promociones vigentes. Para darte la mejor asesoría, por favor indícame qué producto te interesa:\n\n• Batidos\n• Vitaminas\n• Suplementos\n• Control de peso\n\nTambién tenemos planes de membresía con descuentos especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 53
            ],
            [
                'keyword' => 'cuesta',
                'response' => "Nuestros precios varían según el producto y las promociones vigentes. Para darte la mejor asesoría, por favor indícame qué producto te interesa:\n\n• Batidos\n• Vitaminas\n• Suplementos\n• Control de peso\n\nTambién tenemos planes de membresía con descuentos especiales. ¿Te gustaría conocer más?",
                'type' => 'text',
                'show_menu' => true,
                'is_active' => false,
                'order' => 54
            ],
            [
                'keyword' => 'abierto',
                'response' => "🕒 *Horarios de Atención*\n\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "📍 *Ubicación*\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 55
            ],
            [
                'keyword' => 'cerrado',
                'response' => "🕒 *Horarios de Atención*\n\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "📍 *Ubicación*\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 56
            ],
            [
                'keyword' => 'direccion',
                'response' => "📍 *Nuestra Dirección*\n\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "🕒 *Horarios de Atención*\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 57
            ],
            [
                'keyword' => 'ubicacion',
                'response' => "📍 *Nuestra Dirección*\n\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "🕒 *Horarios de Atención*\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 58
            ],
            [
                'keyword' => 'donde',
                'response' => "📍 *Nuestra Dirección*\n\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "🕒 *Horarios de Atención*\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 59
            ],
            [
                'keyword' => 'lugar',
                'response' => "📍 *Nuestra Dirección*\n\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "🕒 *Horarios de Atención*\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 60
            ],
            [
                'keyword' => 'local',
                'response' => "📍 *Nuestra Dirección*\n\n" .
                    "Av. Principal #123\n" .
                    "Ciudad, Estado\n\n" .
                    "🕒 *Horarios de Atención*\n" .
                    "Lunes a Viernes: 9:00 AM - 7:00 PM\n" .
                    "Sábados: 9:00 AM - 2:00 PM\n" .
                    "Domingos: Cerrado\n\n" .
                    "¿En qué más puedo ayudarte?",
                'type' => 'text',
                'show_menu' => true,
                'order' => 61
            ]
        ];

        foreach ($responses as $response) {
            WhatsappChatbotResponse::create($response);
        }
    }
}
