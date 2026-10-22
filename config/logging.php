<?php
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
return [
    'default' => 'json',
    'channels' => [
        'json' => [
            'driver' => 'monolog', 'level' => 'debug',
            'handler' => StreamHandler::class,
            'with' => ['stream' => storage_path('logs/laravel.log')],
            'formatter' => JsonFormatter::class,
        ],
    ],
];
