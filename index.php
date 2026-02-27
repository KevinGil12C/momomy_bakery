<?php

use App\Services\RateLimitService;
use App\config\Router;
use App\Controller\MainController;
use App\Controller\ApiController;
use App\Controller\AuthController;
use App\Controller\AdminController;
use App\Controller\CustomerController;
use App\Controller\PublicController;

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

// Public Catalog & Main Site
$router->get('/', [PublicController::class, 'index']);
$router->get('/catalog', [PublicController::class, 'catalog']);
$router->get('/product/(\d+)', [PublicController::class, 'product']);
$router->get('/contact', [PublicController::class, 'contact']);
$router->get('/quotation', [PublicController::class, 'quotation']);
$router->get('/quotation/([a-zA-Z0-9]+)', [PublicController::class, 'viewQuotation']);
$router->get('/privacy', [PublicController::class, 'privacy']);
$router->get('/terms', [PublicController::class, 'terms']);

// API & Interactions
$router->get('/api/products', [ApiController::class, 'products']);
$router->post('/api/order', [ApiController::class, 'receiveOrder']);
$router->post('/api/contact', [ApiController::class, 'contact']);
$router->post('/api/quotation/request', [ApiController::class, 'requestQuotation']);
$router->get('/api/product/comments/(\d+)', [ApiController::class, 'getProductComments']);
$router->post('/api/product/comment', [ApiController::class, 'postComment']);
$router->post('/api/newsletter/subscribe', [ApiController::class, 'subscribeNewsletter']);
$router->get('/api/newsletter/subscribers', [ApiController::class, 'getSubscribers']);
$router->post('/api/recovery', [ApiController::class, 'recovery']); // Moved from previous Admin Auth Routes section
$router->get('/order/status/(:any)', [ApiController::class, 'orderStatus']); // Moved from System Actions section

// Admin Auth Routes
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/admin/2fa', [AuthController::class, 'show2FA']);
$router->post('/admin/2fa', [AuthController::class, 'verify2FA']);
$router->get('/logout', [AuthController::class, 'logout']);

// Admin Management Routes
$router->get('/admin/dashboard', [AdminController::class, 'dashboard']);
// Inventory Management
$router->get('/admin/inventory', [AdminController::class, 'inventory']);
$router->get('/admin/inventory/create', [AdminController::class, 'createProduct']);
$router->post('/admin/inventory/store', [AdminController::class, 'storeProduct']);
$router->get('/admin/inventory/edit/(\d+)', [AdminController::class, 'editProduct']);
$router->post('/admin/inventory/update/(\d+)', [AdminController::class, 'updateProduct']);
$router->get('/admin/inventory/status/(\d+)', [AdminController::class, 'toggleProductStatus']);

// Category Management
$router->get('/admin/categories', [AdminController::class, 'categories']);
$router->post('/admin/categories/store', [AdminController::class, 'storeCategory']);
$router->get('/admin/categories/delete/(\d+)', [AdminController::class, 'deleteCategory']);

// News Management
$router->get('/admin/news', [AdminController::class, 'news']);
$router->get('/admin/news/create', [AdminController::class, 'createNews']);
$router->post('/admin/news/store', [AdminController::class, 'storeNews']);
$router->get('/admin/news/edit/(\d+)', [AdminController::class, 'editNews']);
$router->post('/admin/news/update/(\d+)', [AdminController::class, 'updateNews']);
$router->get('/admin/news/delete/(\d+)', [AdminController::class, 'deleteNews']);
$router->get('/admin/subscribers', [AdminController::class, 'subscribers']);

// Orders management
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->get('/admin/orders/create', [AdminController::class, 'createOrder']);
$router->post('/admin/orders/store', [AdminController::class, 'storeOrder']);
$router->get('/admin/orders/edit/(:any)', [AdminController::class, 'editOrder']);
$router->post('/admin/orders/update/(:any)', [AdminController::class, 'updateOrder']);
$router->get('/admin/orders/receipt/(:any)', [AdminController::class, 'generateReceipt']);

// Featured Management
$router->get('/admin/featured', [AdminController::class, 'featured']);
$router->post('/admin/featured/update', [AdminController::class, 'updateFeatured']);

$router->get('/admin/quotations', [AdminController::class, 'quotations']);
$router->get('/admin/quotations/create', [AdminController::class, 'createQuotation']);
$router->post('/admin/quotations/store', [AdminController::class, 'storeQuotation']);
$router->get('/admin/quotations/download/(:any)', [AdminController::class, 'downloadQuotation']);

$router->get('/admin/users', [AdminController::class, 'users']);
$router->get('/admin/users/create', [AdminController::class, 'createUser']);
$router->post('/admin/users/store', [AdminController::class, 'storeUser']);

// Customers Management
$router->get('/admin/customers', [AdminController::class, 'customers']);
$router->get('/admin/customers/edit/(:any)', [AdminController::class, 'editCustomer']);
$router->post('/admin/customers/update/(:any)', [AdminController::class, 'updateCustomer']);
$router->get('/admin/customers/delete/(:any)', [AdminController::class, 'deleteCustomer']);
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

// Customer portal (Connect)
$router->get('/connect/order/(:any)', [CustomerController::class, 'viewOrder']);
$router->get('/connect/register', [CustomerController::class, 'showRegister']);
$router->post('/connect/register', [CustomerController::class, 'register']);
$router->get('/connect/login', [CustomerController::class, 'showLogin']);
$router->post('/connect/login', [CustomerController::class, 'login']);
$router->get('/connect/logout', [CustomerController::class, 'logout']);
$router->get('/connect/dashboard', [CustomerController::class, 'dashboard']);

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
