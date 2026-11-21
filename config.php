<?php

declare(strict_types=1);

function envValue(string $name, string $default): string
{
    $value = getenv($name);
    return $value === false || $value === '' ? $default : $value;
}

return [
    'database' => [
        'host' => envValue('APP_DB_HOST', '127.0.0.1'),
        'port' => envValue('APP_DB_PORT', '3306'),
        'name' => envValue('APP_DB_NAME', 'app_comentarios'),
        'user' => envValue('APP_DB_USER', 'root'),
        'password' => envValue('APP_DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
];
