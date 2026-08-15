<?php

declare(strict_types=1);

final class Router
{
    /** @var array<int, array{method: string, pattern: string, paramNames: array<int, string>, handler: callable}> */
    private array $routes = [];

    public function get(string $pattern, callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, callable $handler): void
    {
        $paramNames = [];
        $regex = preg_replace_callback('/\{([a-zA-Z_]+)\}/', function ($matches) use (&$paramNames) {
            $paramNames[] = $matches[1];
            return '([^/]+)';
        }, $pattern);

        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $regex . '$#',
            'paramNames' => $paramNames,
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $path === '' ? '/' : rtrim($path, '/');
        if ($path === '') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                array_shift($matches);
                $params = array_combine($route['paramNames'], $matches);
                call_user_func($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);
        (new PaginaController())->naoEncontrada();
    }
}
