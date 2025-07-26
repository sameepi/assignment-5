<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!file_exists($path)) {
        throw new RuntimeException(sprintf('%s not found', $path));
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (str_starts_with(trim($line), '#')) {
            continue;
        }

        // Parse name=value pairs
        if (str_contains($line, '=')) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^([\'\"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Set the environment variable if not already set
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
}

try {
    loadEnv(dirname(__DIR__) . '/config/.env');
} catch (RuntimeException $e) {
    die('Error loading .env file: ' . $e->getMessage());
}

// Load environment variables
require_once __DIR__ . '/helpers.php';

// Use Composer's autoloader if available, otherwise fall back to custom autoloader
if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__) . '/vendor/autoload.php';
} else {
    // Fallback to custom autoloader if Composer's autoloader is not available
    require_once __DIR__ . '/autoload.php';
}

use App\Core\Session;

// Make sure session is started before generating CSRF token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if it doesn't exist
if (class_exists('App\\Core\\Session')) {
    Session::generateCsrfToken();
} else {
    error_log('Warning: App\\Core\\Session class not found. CSRF token generation skipped.');
}
