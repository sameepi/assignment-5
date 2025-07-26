<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');


// Register the autoloader
require_once dirname(__DIR__) . '/src/autoload.php';

// Define the base path for includes
$basePath = dirname(__DIR__) . '/src';

// List of required files with their expected paths
$requiredFiles = [
    'Core/Database.php',
    'Core/Router.php',
    'Core/Session.php',
    'Controllers/MovieController.php',
    'Models/Api.php',
    'Models/Rating.php',
    'Views/View.php'
];

// Check each required file
echo "<h1>File Check</h1><pre>";
foreach ($requiredFiles as $file) {
    $path = $basePath . '/' . $file;
    if (file_exists($path)) {
        echo "✓ Found: $file\n";
    } else {
        echo "✗ Missing: $file\n";
    }
}
echo "</pre>";

// Test autoloader functionality
echo "<h1>Autoloader Test</h1><pre>";
try {
    // Test loading each class using autoloader
    $classes = [
        'App\\Core\\Database',
        'App\\Core\\Router',
        'App\\Core\\Session',
        'App\\Controllers\\MovieController',
        'App\\Models\\Api',
        'App\\Models\\Rating',
        'App\\Views\\View'
    ];
    
    // Clear any previously loaded classes
    foreach ($classes as $class) {
        if (class_exists($class, false)) {
            $reflection = new ReflectionClass($class);
            $reflection->newInstanceWithoutConstructor();
        }
    }
    
    echo "Testing autoloader...\n\n";
    
    foreach ($classes as $class) {
        // Check if class exists (this will trigger the autoloader)
        if (class_exists($class)) {
            echo "✓ Autoloader loaded: $class\n";
        } else {
            echo "✗ Autoloader failed to load: $class\n";
            
            // Try to manually include the file
            $relativePath = str_replace('App\\', '', $class);
            $filePath = $basePath . '/' . str_replace('\\', '/', $relativePath) . '.php';
            
            if (file_exists($filePath)) {
                require_once $filePath;
                if (class_exists($class, false)) {
                    echo "  ✓ Manually loaded: $class\n";
                } else {
                    echo "  ✗ Could not load class even with manual include: $class\n";
                }
            } else {
                echo "  ✗ File not found: $filePath\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Autoloader test failed: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test class loading
echo "<h1>Class Loading Test</h1><pre>";
try {
    // Test loading each class
    $classes = [
        'App\\Core\\Database',
        'App\\Core\\Router',
        'App\\Core\\Session',
        'App\\Controllers\\MovieController',
        'App\\Models\\Api',
        'App\\Models\\Rating',
        'App\\Views\\View'
    ];
    
    foreach ($classes as $class) {
        if (class_exists($class, false)) { // Pass false to prevent autoloading
            echo "✓ Class loaded: $class\n";
        } else {
            echo "✗ Class not found: $class\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test database connection
echo "<h1>Database Connection Test</h1><pre>";
try {
    require_once $basePath . '/Core/Database.php';
    $db = new App\Core\Database();
    $pdo = $db->getConnection();
    echo "✓ Database connection successful!\n";
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test session
echo "<h1>Session Test</h1><pre>";
try {
    require_once $basePath . '/Core/Session.php';
    App\Core\Session::start();
    echo "✓ Session started successfully\n";
} catch (Exception $e) {
    echo "✗ Session start failed: " . $e->getMessage() . "\n";
}
echo "</pre>";

// Test router
echo "<h1>Router Test</h1><pre>";
try {
    require_once $basePath . '/Core/Router.php';
    $router = new App\Core\Router();
    echo "✓ Router initialized successfully\n";
    
    // Test adding a route
    $router->get('/test', ['App\Controllers\MovieController', 'index']);
    echo "✓ Route added successfully\n";
    
} catch (Exception $e) {
    echo "✗ Router test failed: " . $e->getMessage() . "\n";
}
echo "</pre>";
