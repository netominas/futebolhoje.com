<?php

declare(strict_types=1);

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!is_file($viewFile)) {
            throw new RuntimeException("View não encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $conteudo = ob_get_clean();

        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
        require $layoutFile;
    }

    public static function partial(string $partial, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require __DIR__ . '/../views/partials/' . $partial . '.php';
    }
}
