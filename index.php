<?php

declare(strict_types=1);

require __DIR__ . '/config/config.php';
require __DIR__ . '/core/helpers.php';

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
