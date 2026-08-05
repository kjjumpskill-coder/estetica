<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Мінімальний роутер. Шаблон шляху підтримує іменовані параметри: /blog/{slug}.
 * Значення параметра — будь-що, крім слеша.
 */
final class Router
{
    /** @var array<string,array<int,array{pattern:string,handler:callable}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    /** @var callable|null */
    private $notFound = null;

    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function notFound(callable $handler): void
    {
        $this->notFound = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = '/' . trim(parse_url($uri, PHP_URL_PATH) ?: '/', '/');
        $method = $method === 'HEAD' ? 'GET' : strtoupper($method);

        foreach ($this->routes[$method] ?? [] as $route) {
            if (preg_match($route['pattern'], $path, $matches) === 1) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                ($route['handler'])($params);

                return;
            }
        }

        http_response_code(404);

        if ($this->notFound !== null) {
            ($this->notFound)([]);
        }
    }

    private function add(string $method, string $path, callable $handler): void
    {
        $pattern = preg_replace(
            '#\\\{([a-z_]+)\\\}#i',
            '(?P<$1>[^/]+)',
            preg_quote('/' . trim($path, '/'), '#')
        );

        $this->routes[$method][] = [
            'pattern' => '#^' . $pattern . '$#u',
            'handler' => $handler,
        ];
    }
}
