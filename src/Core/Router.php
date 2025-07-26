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
                // Validate CSRF token for POST requests
                if ($method === 'POST') {
                    $this->validateCsrfToken();
                }
                
                $this->callHandler($route['handler'], array_slice($matches, 1));
                return;
            }
        }

        $this->handleNotFound();
    }
    
    /**
     * Validate the CSRF token for the current request
     * 
     * @throws Exception If the CSRF token is invalid
     */
    private function validateCsrfToken(): void
    {
        // Skip CSRF validation for API endpoints if needed
        if ($this->isApiRequest()) {
            return;
        }
        
        $token = $this->getCsrfTokenFromRequest();
        
        if (!Session::validateCsrfToken($token)) {
            if ($this->isAjaxRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Invalid CSRF token']);
                exit;
            } else {
                throw new Exception('Invalid CSRF token');
            }
        }
    }
    
    /**
     * Get the CSRF token from the request
     */
    private function getCsrfTokenFromRequest(): string
    {
        // Check for token in headers first (for AJAX requests)
        $headers = getallheaders();
        if (isset($headers['X-CSRF-TOKEN'])) {
            return $headers['X-CSRF-TOKEN'];
        }
        
        // Fall back to POST data for form submissions
        return $_POST['_token'] ?? '';
    }
    
    /**
     * Check if the current request is an AJAX request
     */
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Check if the current request is an API request
     */
    private function isApiRequest(): bool
    {
        return str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
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