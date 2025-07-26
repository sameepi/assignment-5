<?php

declare(strict_types=1);

function url(string $path = ''): string
{
    static $baseUrl = null;
    
    if ($baseUrl === null) {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
    }
    
    $path = ltrim($path, '/');
    return $baseUrl . ($path ? '/' . $path : '');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}
