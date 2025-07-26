<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\MovieController;
use App\Core\Database;
use App\Core\Router;
use App\Core\Session;

// Start session
Session::start();

// Initialize database
$database = new Database();
$pdo = $database->getConnection();

// Initialize router
$router = new Router();

// Define routes following instructor's structure
$router->get('/', [MovieController::class, 'index']);
$router->get('/movie', [MovieController::class, 'index']);
$router->post('/movie/search', [MovieController::class, 'search']);
$router->get('/movie/review/{title}/{rating}', [MovieController::class, 'review']);

// Handle the request
$router->handleRequest($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);