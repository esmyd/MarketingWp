<?php

return [
    'modules' => [
        'dashboard' => [
            'label' => 'Dashboard',
            'icon' => 'fa-chart-line',
            'permissions' => [
                'dashboard.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'dashboard.view' => ['label' => 'Ver resumen y consumo', 'type' => 'action'],
            ],
        ],
        'chats' => [
            'label' => 'Chats WhatsApp',
            'icon' => 'fa-comments',
            'permissions' => [
                'chats.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'chats.view' => ['label' => 'Ver conversaciones', 'type' => 'action'],
                'chats.open' => ['label' => 'Abrir chat', 'type' => 'action'],
                'chats.send' => ['label' => 'Enviar mensajes', 'type' => 'action'],
                'chats.toggle_bot' => ['label' => 'Activar / desactivar bot', 'type' => 'action'],
            ],
        ],
        'orders' => [
            'label' => 'Pedidos',
            'icon' => 'fa-shopping-cart',
            'permissions' => [
                'orders.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'orders.view' => ['label' => 'Ver pedidos', 'type' => 'action'],
                'orders.update' => ['label' => 'Cambiar estado de pedidos', 'type' => 'action'],
            ],
        ],
        'marketing_flow' => [
            'label' => 'Flujo del bot',
            'icon' => 'fa-project-diagram',
            'permissions' => [
                'marketing_flow.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'marketing_flow.view' => ['label' => 'Ver flujo', 'type' => 'action'],
                'marketing_flow.update' => ['label' => 'Editar flujo', 'type' => 'action'],
            ],
        ],
        'menus' => [
            'label' => 'Categorías',
            'icon' => 'fa-folder-open',
            'permissions' => [
                'menus.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'menus.view' => ['label' => 'Ver categorías', 'type' => 'action'],
                'menus.update' => ['label' => 'Crear / editar categorías', 'type' => 'action'],
            ],
        ],
        'products' => [
            'label' => 'Productos',
            'icon' => 'fa-box-open',
            'permissions' => [
                'products.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'products.view' => ['label' => 'Ver productos', 'type' => 'action'],
                'products.update' => ['label' => 'Crear / editar productos', 'type' => 'action'],
            ],
        ],
        'chatbot' => [
            'label' => 'Configuración del bot',
            'icon' => 'fa-sliders-h',
            'permissions' => [
                'chatbot.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'chatbot.view' => ['label' => 'Ver configuración', 'type' => 'action'],
                'chatbot.update' => ['label' => 'Editar configuración', 'type' => 'action'],
            ],
        ],
        'pricing_settings' => [
            'label' => 'Tarifas Meta (interno)',
            'icon' => 'fa-tags',
            'permissions' => [
                'pricing_settings.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'pricing_settings.view' => ['label' => 'Ver tarifas internas', 'type' => 'action'],
                'pricing_settings.update' => ['label' => 'Editar tarifas internas', 'type' => 'action'],
            ],
        ],
        'users' => [
            'label' => 'Usuarios',
            'icon' => 'fa-user-gear',
            'permissions' => [
                'users.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'users.view' => ['label' => 'Ver usuarios', 'type' => 'action'],
                'users.create' => ['label' => 'Crear usuario', 'type' => 'action'],
                'users.update' => ['label' => 'Editar usuario', 'type' => 'action'],
                'users.delete' => ['label' => 'Eliminar usuario', 'type' => 'action'],
            ],
        ],
        'roles' => [
            'label' => 'Roles y permisos',
            'icon' => 'fa-key',
            'permissions' => [
                'roles.menu' => ['label' => 'Ver en menú', 'type' => 'menu'],
                'roles.view' => ['label' => 'Ver roles', 'type' => 'action'],
                'roles.update' => ['label' => 'Configurar permisos', 'type' => 'action'],
            ],
        ],
    ],

    'default_roles' => [
        'super_admin' => [
            'name' => 'Super Administrador',
            'description' => 'Acceso total, incluyendo tarifas Meta internas.',
            'is_system' => true,
            'permissions' => '*',
        ],
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Gestión completa del bot, catálogo y operaciones.',
            'is_system' => true,
            'permissions' => [
                'dashboard.menu', 'dashboard.view',
                'chats.menu', 'chats.view', 'chats.open', 'chats.send', 'chats.toggle_bot',
                'orders.menu', 'orders.view', 'orders.update',
                'marketing_flow.menu', 'marketing_flow.view', 'marketing_flow.update',
                'menus.menu', 'menus.view', 'menus.update',
                'products.menu', 'products.view', 'products.update',
                'chatbot.menu', 'chatbot.view', 'chatbot.update',
                'users.menu', 'users.view', 'users.create', 'users.update',
            ],
        ],
        'agent' => [
            'name' => 'Agente de ventas',
            'description' => 'Atiende chats y gestiona pedidos.',
            'is_system' => true,
            'permissions' => [
                'dashboard.menu', 'dashboard.view',
                'chats.menu', 'chats.view', 'chats.open', 'chats.send', 'chats.toggle_bot',
                'orders.menu', 'orders.view', 'orders.update',
            ],
        ],
        'viewer' => [
            'name' => 'Consultor / Solo lectura',
            'description' => 'Consulta reportes sin modificar configuración.',
            'is_system' => true,
            'permissions' => [
                'dashboard.menu', 'dashboard.view',
                'chats.menu', 'chats.view', 'chats.open',
                'orders.menu', 'orders.view',
            ],
        ],
    ],
];
