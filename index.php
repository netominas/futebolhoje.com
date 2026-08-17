<?php

declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/core/helpers.php';

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'),
]);
session_start();

spl_autoload_register(function (string $class): void {
    foreach (['core', 'controllers', 'models'] as $dir) {
        $file = __DIR__ . "/{$dir}/{$class}.php";
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

require __DIR__ . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
