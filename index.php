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

// Default route (Home)
$router->get('/', [AuthController::class, 'showLogin']);

// API Routes (For Vercel Frontend)
$router->post('/api/contact', [ApiController::class, 'receiveContact']);
$router->post('/api/order', [ApiController::class, 'receiveOrder']); // New order from front
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
$router->get('/admin/inventory/create', [AdminController::class, 'createProduct']);
$router->post('/admin/inventory/store', [AdminController::class, 'storeProduct']);
$router->get('/admin/inventory/edit/(:any)', [AdminController::class, 'editProduct']);
$router->post('/admin/inventory/update/(:any)', [AdminController::class, 'updateProduct']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/orders/create', [AdminController::class, 'createOrder']);
$router->post('/admin/orders/store', [AdminController::class, 'storeOrder']);
$router->get('/admin/orders/edit/(:any)', [AdminController::class, 'editOrder']);
$router->post('/admin/orders/update/(:any)', [AdminController::class, 'updateOrder']);
$router->get('/admin/orders/receipt/(:any)', [AdminController::class, 'generateReceipt']);
$router->get('/admin/quotations', [AdminController::class, 'quotations']);
$router->get('/admin/quotations/create', [AdminController::class, 'createQuotation']);
$router->post('/admin/quotations/store', [AdminController::class, 'storeQuotation']);
$router->get('/admin/quotations/download/(:any)', [AdminController::class, 'downloadQuotation']);

// User Management & Profile
$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/users/create', [AdminController::class, 'createUser']);
$router->post('/admin/users/store', [AdminController::class, 'storeUser']);
$router->get('/admin/profile', [AdminController::class, 'profile']);
$router->post('/admin/profile/update', [AdminController::class, 'updateProfile']);
$router->get('/admin/settings', [AdminController::class, 'settings']);
$router->post('/admin/settings/update', [AdminController::class, 'updateSettings']);
$router->get('/admin/backups', [AdminController::class, 'backups']);
$router->post('/admin/backups/generate', [AdminController::class, 'generateBackup']);
$router->get('/admin/backups/download/(:any)', [AdminController::class, 'downloadBackupFile']);
$router->get('/admin/backups/delete/(:any)', [AdminController::class, 'deleteBackupFile']);
$router->get('/admin/contacts', [AdminController::class, 'contacts']);
$router->get('/admin/contacts/read/(:any)', [AdminController::class, 'markAsRead']);
$router->get('/admin/contacts/delete/(:any)', [AdminController::class, 'deleteContact']);
$router->get('/order/status/(:any)', [ApiController::class, 'orderStatus']);

// System Actions
$router->get('/admin/inventory/delete/(:any)', [AdminController::class, 'deleteProduct']);
$router->get('/admin/orders/delete/(:any)', [AdminController::class, 'deleteOrder']);
$router->get('/admin/quotations/delete/(:any)', [AdminController::class, 'deleteQuotationEntry']);
$router->get('/admin/quotations/edit/(:any)', [AdminController::class, 'editQuotation']);

// API & Public External Endpoints
$router->post('/api/contact', [ApiController::class, 'receiveContact']);
$router->post('/api/order', [ApiController::class, 'receiveOrder']);
$router->post('/api/quotation/request', [ApiController::class, 'requestQuotation']);

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
