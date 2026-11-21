<?php

declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($requestPath === '/api.php') {
    require __DIR__ . '/api.php';
    return;
}

$staticFiles = [
    '/styles.css' => __DIR__ . '/public/styles.css',
    '/app.js' => __DIR__ . '/public/app.js',
];

if (isset($staticFiles[$requestPath])) {
    $file = $staticFiles[$requestPath];
} elseif ($requestPath === '/') {
    $file = __DIR__ . '/public/index.html';
} else {
    $file = __DIR__ . '/public' . $requestPath;
}
if (is_file($file)) {
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    $contentTypes = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
    ];
    header('Content-Type: ' . ($contentTypes[$extension] ?? 'application/octet-stream'));
    readfile($file);
    return;
}

http_response_code(404);
echo 'No encontrado';
