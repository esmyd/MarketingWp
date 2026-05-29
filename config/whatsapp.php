<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the WhatsApp Business API.
    | Using the specific environment variables from the user's setup.
    |
    */

    // WhatsApp Business API version (typing indicators requieren v23.0+)
    'api_version' => env('WHATSAPP_API_VERSION', 'v23.0'),

    // Indicador "escribiendo..." en WhatsApp del cliente
    'typing_indicator_enabled' => env('WHATSAPP_TYPING_INDICATOR', true),

    // Pausa (ms) entre mostrar "escribiendo..." y enviar respuesta del bot (para que el usuario lo vea)
    'bot_reply_delay_ms' => env('WHATSAPP_BOT_REPLY_DELAY_MS', 2500),

    // Base URL for API requests
    'api_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),

    // Webhook verification token
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN'),

    // WhatsApp Business API Token
    'token' => env('WHATSAPP_TOKEN'),

    // Business Phone Number
    'phone_number' => env('WHATSAPP_PHONE_NUMBER'),

    // Phone Number ID (15 dígitos) para llamadas a la API
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),

    // Default language for templates
    'default_language' => 'es',

    // Message status update delay in seconds
    'status_update_delay' => 60,

    // Maximum retry attempts for failed messages
    'max_retry_attempts' => 3,

    // Retry delay between attempts in seconds
    'retry_delay' => 60,

    // Webhook URL for receiving updates
    'webhook_url' => env('WHATSAPP_WEBHOOK_URL'),

    // Default message template category
    'default_template_category' => 'MARKETING',

    // Message template approval settings
    'template_approval' => [
        'auto_approve' => false,
        'approval_required' => true,
    ],

    // Chatbot settings
    'chatbot' => [
        'enabled' => true,
        'default_response' => 'Lo siento, no entiendo tu mensaje. Por favor, intenta con otra palabra clave.',
        'max_conversation_duration' => 3600, // 1 hour in seconds
    ],

    // Business Profile Settings (use your custom variable names)
    'business_id' => env('WHATSAPP_BUSINESS_ID'),
];
