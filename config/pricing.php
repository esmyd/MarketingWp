<?php

return [
    'sales_whatsapp' => env('SALES_WHATSAPP_NUMBER', env('DEMO_WHATSAPP_NUMBER', '593994281769')),

    /*
    | Demo pública: bot WhatsApp + acceso al panel
    */
    'demo' => [
        'whatsapp_number' => env('DEMO_WHATSAPP_NUMBER', '593994281769'),
        'whatsapp_message' => '¡Hola! Quiero probar el demo del bot de WhatsApp 🤖',
        'panel_user' => env('DEMO_PANEL_USER', 'gosorio'),
        'panel_password' => env('DEMO_PANEL_PASSWORD', 'go123'),
    ],

    /*
    | Margen sobre tarifa Meta de referencia (gestión, variaciones, imprevistos)
    */
    'meta_markup' => 1.30,

    /*
    | Tipos de conversación Meta visibles para este cliente (dashboard + /planes).
    | Se puede cambiar desde el panel Tarifas Meta (super admin).
    */
    'enabled_conversation_categories' => [
        'service' => true,
        'utility' => true,
        'marketing' => false,
        'authentication' => false,
    ],

    'plans' => [
        'starter' => [
            'name' => 'Starter',
            'label' => 'Plan Esencial',
            'price' => 60,
            'price_label' => '$60/mes',
            'from' => false,
            'cta' => 'Elegir Starter',
        ],
        'pro' => [
            'name' => 'Pro',
            'label' => 'Plan Profesional',
            'price' => 90,
            'price_label' => '$90/mes',
            'from' => false,
            'cta' => 'Elegir Pro',
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'label' => 'Plan Empresarial',
            'price' => 130,
            'price_label' => 'desde $130/mes',
            'from' => true,
            'cta' => 'Solicitar cotización',
        ],
    ],

    /*
    | Tarifas base Meta (USD) — en la vista se aplican +30% (meta_markup)
    */
    'meta_rates' => [
        'region' => 'Ecuador / Latam',
        'currency' => 'USD',
        'per_conversation' => [
            'service' => [
                'min' => 0.012,
                'max' => 0.022,
                'icon' => '💬',
                'label' => 'Cuando un cliente te escribe',
                'description' => 'Alguien te manda hola, pregunta precios, usa el menú o compra. El chat lo inicia la persona.',
            ],
            'utility' => [
                'min' => 0.028,
                'max' => 0.042,
                'icon' => '📋',
                'label' => 'Avisos que envía el bot',
                'description' => 'Confirmación de pedido, “tu pago fue recibido”, cambio de estado — mensajes informativos, no promociones.',
            ],
            'marketing' => [
                'min' => 0.055,
                'max' => 0.085,
                'icon' => '📢',
                'label' => 'Promociones que tú envías',
                'description' => 'Ofertas, recordatorios o campañas masivas a tu lista de contactos (plan Pro). Tú inicias el mensaje.',
            ],
            'authentication' => [
                'min' => 0.018,
                'max' => 0.032,
                'icon' => '🔐',
                'label' => 'Códigos de verificación',
                'description' => 'OTP o códigos de acceso, si los usas en tu flujo.',
            ],
        ],
    ],

    'meta_scenarios' => [
        [
            'id' => 'small',
            'title' => 'Negocio pequeño',
            'plan_hint' => 'Starter',
            'service_conv' => 250,
            'utility_conv' => 50,
            'marketing_conv' => 0,
            'description' => 'El bot atiende consultas puntuales. Casi nadie te escribe masivamente y no haces envíos promocionales.',
        ],
        [
            'id' => 'medium',
            'title' => 'Negocio en crecimiento',
            'plan_hint' => 'Pro',
            'service_conv' => 600,
            'utility_conv' => 120,
            'marketing_conv' => 200,
            'description' => 'Varios clientes al mes usan el bot para comprar o preguntar, y de vez en cuando mandas ofertas a quien ya te conoce.',
        ],
        [
            'id' => 'active',
            'title' => 'Marketing activo',
            'plan_hint' => 'Pro',
            'service_conv' => 1200,
            'utility_conv' => 300,
            'marketing_conv' => 800,
            'description' => 'Mucho tráfico diario y campañas frecuentes: promos, recordatorios y reactivación de clientes.',
        ],
        [
            'id' => 'enterprise',
            'title' => 'Alto volumen',
            'plan_hint' => 'Enterprise',
            'service_conv' => 2500,
            'utility_conv' => 600,
            'marketing_conv' => 2000,
            'description' => 'Operación grande con cientos de chats y envíos masivos cada mes.',
        ],
    ],
];
