<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once dirname(__DIR__) . '/src/bootstrap.php';

require_once dirname(__DIR__) . '/src/autoload.php';

use App\Controllers\MovieController;
use App\Core\Database;
use App\Core\Router;
use App\Core\Session;

Session::start();

try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

$router = new Router();

$router->get('/', [MovieController::class, 'index']);
$router->get('/movie', [MovieController::class, 'index']);
$router->get('/movie/search', [MovieController::class, 'search']);
$router->post('/movie/search', [MovieController::class, 'search']);
$router->get('/movie/review/{title}/{rating}', [MovieController::class, 'review']);

$requestMethod = $_SERVER['REQUEST_METHOD'];

$requestUri = '/';
if (isset($_GET['url'])) {
    $requestUri = '/' . trim($_GET['url'], '/');
    if (empty($requestUri)) {
        $requestUri = '/';
    }
}

error_log("Handling request: $requestMethod $requestUri");

try {
    $router->handleRequest($requestUri, $requestMethod);
} catch (Exception $e) {
    http_response_code(500);
    echo "<h1>500 Internal Server Error</h1>";
    echo "<p>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    if (ini_get('display_errors')) {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
    error_log("Error handling request: " . $e->getMessage() . "\n" . $e->getTraceAsString());
}