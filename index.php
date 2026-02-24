<?php

use App\Services\RateLimitService;
use App\config\Router;
use App\Controller\MainController;
use App\Controller\ApiController;
use App\Controller\AuthController;
use App\Controller\AdminController;

require_once __DIR__ . '/vendor/autoload.php';

// Composer Autoloader fallback
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = __DIR__ . '/app/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) return;
        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        if (file_exists($file)) require $file;
    });
}

// 1. Rate Limiting Check
$rateLimiter = new RateLimitService(200, 3600);
if (!$rateLimiter->isAllowed($_SERVER['REMOTE_ADDR'])) {
    http_response_code(429);
    echo "Too Many Requests. Please try again later.";
    exit;
}

// 2. Router Setup
$router = new Router();

// Define Routes
$router->get('/', [MainController::class, 'index']);
$router->get('/privacy', [MainController::class, 'privacy']);
$router->get('/terms', [MainController::class, 'terms']);
$router->get('/support', [MainController::class, 'support']);
$router->get('/contact', [MainController::class, 'contact']);

// API Routes
$router->post('/api/contact', [ApiController::class, 'receiveContact']);
$router->post('/api/recovery', [ApiController::class, 'recovery']);

// Admin Auth Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/admin/2fa', [AuthController::class, 'show2FA']);
$router->post('/admin/2fa', [AuthController::class, 'verify2FA']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin Management Routes
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$router->get('/admin/inventory', [AdminController::class, 'inventory']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/quotations', [AdminController::class, 'quotations']);

// Example of resource routing (for later implementation)
// $router->resource('products', ProductController::class);

// 3. Resolve Request
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalize URI for local XAMPP and subfolders
$uri = str_replace('/momomy_bakery', '', $uri);

// Ensure it starts with / and remove trailing slash (except for root)
$uri = '/' . ltrim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}

$router->resolve($uri, $_SERVER['REQUEST_METHOD']);
