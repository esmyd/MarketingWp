<?php

return [
    /*
    | Mínimo de ítems (líneas) en el carrito para sugerir el formulario web.
    */
    'min_cart_lines' => (int) env('BULK_ORDER_MIN_LINES', 3),

    /*
    | Horas de validez del enlace del formulario.
    */
    'token_ttl_hours' => (int) env('BULK_ORDER_TOKEN_TTL', 24),
];
