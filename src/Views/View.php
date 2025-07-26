<?php

declare(strict_types=1);

namespace App\Views;

use Exception;

final class View
{
    private string $viewsPath;

    public function __construct()
    {
        $this->viewsPath = __DIR__ . '/../../resources/views/';
    }

    public function render(string $view, array $data = []): void
    {
        $viewFile = $this->viewsPath . $view . '.php';

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: {$viewFile}");
        }

        // Make url() helper available in views
        if (!function_exists('url')) {
            function url(string $path = ''): string
            {
                static $baseUrl = null;
                
                if ($baseUrl === null) {
                    $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
                }
                
                $path = ltrim($path, '/');
                return $baseUrl . ($path ? '/' . $path : '');
            }
        }

        // Extract data to variables
        extract($data, EXTR_SKIP);

        // Start output buffering
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Include layout
        include $this->viewsPath . 'layouts/app.php';
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}   