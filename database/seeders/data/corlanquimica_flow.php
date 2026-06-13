<?php

use App\Enums\MarketingStepKey;

/**
 * Mensajes del flujo comercial — Corlan Química S.A.
 * Tono B2B: claro, formal-cercano, orientado a ventas corporativas.
 */
return [
    MarketingStepKey::WELCOME => [
        'message' => "Estimado(a) *{{nombre}}*,\n\nLe damos la bienvenida a *{{nombre_empresa}}*, proveedor líder en insumos de limpieza, higiene industrial, químicos y soluciones para empresas en Ecuador.\n\nSoy *{{nombre_bot}}*, su asistente comercial. Puedo ayudarle con catálogo, cotizaciones y seguimiento de pedidos.\n\n¿Cómo desea continuar?",
        'type'    => 'text',
    ],

    MarketingStepKey::MAIN_MENU => [
        'message' => "*Menú principal*\n*{{nombre_empresa}}*\n\nSeleccione una opción para continuar:",
        'type'    => 'button',
        'buttons' => [
            ['id' => 'menu_productos', 'title' => '🧪 Catálogo',      'action' => 'products'],
            ['id' => 'menu_pedido',    'title' => '📦 Mis pedidos',   'action' => 'orders'],
            ['id' => 'menu_info',      'title' => 'ℹ️ Información',   'action' => 'info'],
        ],
    ],

    MarketingStepKey::PRODUCTS_MENU => [
        'message'           => "*Catálogo de productos*\n\nContamos con *{{total_productos}} referencias* en *{{total_categorias}} líneas*: limpieza industrial, químicos, cafetería, oficina, sector bananero, agroquímicos y equipos de protección.\n\nSeleccione una categoría o escriba el código SKU (ej.: *CQ001*).",
        'type'              => 'list',
        'catalog_source'    => 'categories',
        'max_product_rows'  => 8,
        'include_navigation'=> true,
        'list'              => ['button' => 'Ver categorías', 'sections' => []],
    ],

    MarketingStepKey::ORDERS_MENU => [
        'message' => "*Seguimiento de pedidos*\n\nConsulte el estado de sus órdenes registradas con *{{nombre_empresa}}*.\n\nPara actualizaciones comerciales o despachos, nuestro equipo está disponible al *{{telefono_soporte}}* en horario *{{horario_atencion}}*.",
        'type'    => 'text',
    ],

    MarketingStepKey::INFO_MENU => [
        'message' => "*Centro de información*\n*{{nombre_empresa}}*\n\nSeleccione el tema que necesita consultar:",
        'type'    => 'list',
        'list'    => [
            'button'   => 'Ver opciones',
            'sections' => [
                [
                    'title' => 'Atención comercial',
                    'rows'  => [
                        ['id' => 'horarios', 'title' => 'Horarios',        'description' => 'Oficinas y bodegas',           'action' => 'horarios'],
                        ['id' => 'contacto', 'title' => 'Contacto',        'description' => 'Teléfonos y correos',          'action' => 'contacto'],
                        ['id' => 'asesoria', 'title' => 'Ejecutivo ventas','description' => 'Atención personalizada',       'action' => 'asesoria'],
                    ],
                ],
                [
                    'title' => 'Operaciones',
                    'rows'  => [
                        ['id' => 'pagos',  'title' => 'Formas de pago',  'description' => 'Transferencia, efectivo, crédito','action' => 'pagos'],
                        ['id' => 'envios', 'title' => 'Entregas',        'description' => 'Despachos y cobertura',         'action' => 'envios'],
                        ['id' => 'redes',  'title' => 'Redes y web',     'description' => 'Sitio y redes sociales',        'action' => 'redes'],
                    ],
                ],
            ],
        ],
    ],

    MarketingStepKey::CART_SUMMARY => [
        'message' => "*Resumen de su pedido*\n\nProductos seleccionados: *{{cantidad_items}}*\nTotal referencial: *{{moneda}} {{total}}*\n\nLos valores finales se confirman según stock, volumen y condiciones comerciales vigentes.\n\n¿Desea continuar?",
        'type'    => 'button',
        'buttons' => [
            ['id' => 'checkout',       'title' => '✅ Finalizar',     'action' => 'checkout'],
            ['id' => 'menu_productos', 'title' => '➕ Agregar más',   'action' => 'products'],
            ['id' => 'menu_principal', 'title' => '🏠 Menú principal','action' => 'main_menu'],
        ],
    ],

    MarketingStepKey::CHECKOUT => [
        'message' => "*Confirmación y pago*\n\nPara procesar su pedido con *{{nombre_empresa}}*, seleccione su forma de pago preferida:\n\n• Transferencia o depósito bancario\n• Efectivo en oficinas — Guayaquil\n• Crédito comercial (clientes corporativos)\n\nUn ejecutivo validará stock, totales definitivos y coordinará la entrega en Guayaquil o envío a provincia.\n\n📞 *{{telefono_soporte}}* · {{horario_atencion}}",
        'type'    => 'text',
    ],

    MarketingStepKey::PAYMENT_PROOF => [
        'message'         => "*Registro de comprobante*\n\nPedido: *{{numero_pedido}}*\nTotal: *{{moneda}} {{total}}*\nMétodo: *{{metodo_pago}}*\n\nEnvíe una imagen o PDF del comprobante de pago. Verifique que incluya monto, fecha y número de referencia.",
        'type'            => 'text',
        'require_proof'   => true,
        'require_for_methods' => ['transferencia', 'tarjeta'],
        'success_message' => "*Comprobante recibido*\n\nHemos registrado su comprobante del pedido *{{numero_pedido}}*. Nuestro equipo comercial lo verificará y le confirmará por este medio a la brevedad.\n\nGracias por confiar en *{{nombre_empresa}}*.",
    ],

    MarketingStepKey::AGENT_HANDOFF => [
        'message' => "*Atención personalizada*\n\nEstamos derivando su conversación a un ejecutivo comercial de *{{nombre_empresa}}*.\n\n⏱ Tiempo estimado de respuesta: *5 a 15 minutos* en horario laboral.\n📅 *{{horario_atencion}}*\n\nTambién puede contactarnos directamente al *{{telefono_soporte}}*.\n\nAgradecemos su preferencia.",
        'type'    => 'text',
    ],

    MarketingStepKey::FALLBACK_MESSAGE => [
        'message' => "Disculpe, no hemos identificado su consulta.\n\nPuede utilizar el menú principal o escribir:\n• *hola* — inicio\n• *productos* — catálogo\n• *pedidos* — seguimiento\n• *contacto* — datos comerciales\n\n¿En qué podemos ayudarle?",
        'type'    => 'button',
        'buttons' => [
            ['id' => 'menu_principal', 'title' => '🏠 Menú principal', 'action' => 'main_menu'],
            ['id' => 'menu_productos', 'title' => '🧪 Ver catálogo',   'action' => 'products'],
            ['id' => 'menu_agent',     'title' => '👤 Ejecutivo',      'action' => 'agent'],
        ],
    ],
];
