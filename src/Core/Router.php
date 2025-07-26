<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
    }

    public function handleRequest(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = $this->convertToRegex($route['path']);
            if (preg_match($pattern, $path, $matches)) {
                $this->callHandler($route['handler'], array_slice($matches, 1));
                return;
            }
        }

        $this->handleNotFound();
    }

    private function convertToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    private function callHandler(array $handler, array $params): void
    {
        [$className, $methodName] = $handler;
        
        if (!class_exists($className)) {
            throw new InvalidArgumentException("Controller class {$className} not found");
        }

        $controller = new $className();
        
        if (!method_exists($controller, $methodName)) {
            throw new InvalidArgumentException("Method {$methodName} not found in {$className}");
        }

        call_user_func_array([$controller, $methodName], $params);
    }

    private function handleNotFound(): void
    {
        http_response_code(404);
        echo "404 - Page not found";
    }
}