<?php
return [
    'name' => env('APP_NAME', 'CP5 Pedidos'), 'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false), 'url' => env('APP_URL', 'http://127.0.0.1:8000'),
    'timezone' => 'UTC', 'locale' => 'pt_BR', 'fallback_locale' => 'en',
    'cipher' => 'AES-256-CBC', 'key' => env('APP_KEY'),
];
