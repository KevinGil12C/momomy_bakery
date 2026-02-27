<?php

namespace App\Controller;

use App\Models\Database;
use App\Services\PdfService;
use App\Services\EmailService;

/**
 * AdminController handles the internal management of the bakery
 */
class AdminController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();

        // Security Check
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect('login');
        }

        $this->db = new Database();

        // Share stats globally in Twig for the header
        $stats = [
            'pending_orders' => count($this->db->select('orders', ['status' => 'pending'])),
            'unread_messages' => count($this->db->select('contacts', ['is_read' => 0])),
            'unread_quotations' => count($this->db->select('quotations', ['status' => 'draft']))
        ];
        $this->twig->addGlobal('stats', $stats);
    }

    /**
     * Main Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_products' => $this->db->countAll('products'),
            'total_orders'   => $this->db->countAll('orders'),
            'pending_orders' => count($this->db->select('orders', ['status' => 'pending'])),
            'unread_messages' => count($this->db->select('contacts', ['is_read' => 0]))
        ];

        // Recent orders
        $recentOrders = $this->db->getAll('orders', [], 'created_at DESC', 5);

        // Unread messages
        $unreadMessages = $this->db->getAll('contacts', ['is_read' => 0], 'created_at DESC', 5);

        $this->showView('admin/dashboard.twig', [
            'title' => 'Dashboard Momomy',
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'unread_messages_list' => $unreadMessages
        ]);
    }

    /**
     * Inventory management with Pagination
     */
    public function inventory()
    {
        $currentPage = $_GET['page'] ?? 1;
        $itemsPerPage = 10;
        $totalItems = $this->db->countAll('products');

        $pager = $this->getPaginationData($totalItems, $itemsPerPage, $currentPage);

        $products = $this->db->getJoin('products', 'products.*, categories.name as category', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ], [], 'products.id DESC', $pager['limit'], $pager['offset']);

        $this->showView('admin/inventory/index.twig', [
            'title' => 'Gestión de Inventario',
            'products' => $products,
            'pager' => $pager
        ]);
    }

    /**
     * Featured Management (Special of the Week and Home Specialties)
     */
    public function featured()
    {
        $allActive = $this->db->getJoin('products', 'products.*, categories.name as category', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ], ['is_active' => 1], 'name ASC');

        $specialOfWeek = null;
        $specialties = [];

        foreach ($allActive as &$p) {
            $p['image_url'] = $this->normalizeImageUrl($p['image_url']);
            if ($p['is_special_of_week']) $specialOfWeek = $p;
            if ($p['is_specialty']) $specialties[] = $p;
        }

        $this->showView('admin/featured/index.twig', [
            'title' => 'Gestión de Destacados',
            'all_products' => $allActive,
            'special_of_week' => $specialOfWeek,
            'specialties' => $specialties
        ]);
    }

    /**
     * Update Featured Status (AJAX or Form)
     */
    public function updateFeatured()
    {
        try {
            if (!isset($_POST['id']) || !isset($_POST['type']) || !isset($_POST['value'])) {
                throw new \Exception("Datos incompletos.");
            }

            $id = $_POST['id'];
            $type = $_POST['type'];
            $value = (int)$_POST['value'];

            if (!in_array($type, ['is_specialty', 'is_special_of_week'])) {
                throw new \Exception("Tipo de destacado no válido.");
            }

            // If it's special of the week, ensure only one is active
            if ($type === 'is_special_of_week' && $value === 1) {
                // Remove special status from all products
                $this->db->rawQuery("UPDATE products SET is_special_of_week = 0");
            }

            $success = $this->db->update('products', [$type => $value], ['id' => $id]);

            if (!$success) {
                throw new \Exception("No se pudo actualizar la base de datos.");
            }

            $this->jsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show create product form
     */
    public function createProduct()
    {
        $categories = $this->db->getAll('categories');
        $this->showView('admin/inventory/edit.twig', [
            'title' => 'Nuevo Postre',
            'categories' => $categories
        ]);
    }

    /**
     * Store new product
     */
    public function storeProduct()
    {
        $imageUrl = $this->handleImageUpload();

        $data = [
            'category_id'        => $_POST['category_id'],
            'name'               => $_POST['name'],
            'description'        => $_POST['description'],
            'ingredients'        => $_POST['ingredients'] ?? '',
            'characteristics'    => $_POST['characteristics'] ?? '',
            'price'              => $_POST['price'],
            'stock'              => $_POST['stock'],
            'image_url'          => $imageUrl,
            'is_specialty'       => isset($_POST['is_specialty']) ? 1 : 0,
            'is_special_of_week' => isset($_POST['is_special_of_week']) ? 1 : 0,
            'is_active'          => 1
        ];

        $this->db->insert('products', $data);
        $this->redirect('admin/inventory');
    }

    /**
     * Show edit product form
     */
    public function editProduct($id)
    {
        $product = $this->db->getOne('products', ['id' => $id]);
        $categories = $this->db->getAll('categories');

        $this->showView('admin/inventory/edit.twig', [
            'title' => 'Editar Postre',
            'product' => $product,
            'categories' => $categories
        ]);
    }

    /**
     * Update existing product
     */
    public function updateProduct($id)
    {
        $data = [
            'category_id'        => $_POST['category_id'],
            'name'               => $_POST['name'],
            'description'        => $_POST['description'],
            'ingredients'        => $_POST['ingredients'] ?? '',
            'characteristics'    => $_POST['characteristics'] ?? '',
            'price'              => $_POST['price'],
            'stock'              => $_POST['stock'],
            'is_specialty'       => isset($_POST['is_specialty']) ? 1 : 0,
            'is_special_of_week' => isset($_POST['is_special_of_week']) ? 1 : 0,
            'is_active'          => isset($_POST['is_active']) ? 1 : 0
        ];

        $imageUrl = $this->handleImageUpload();
        if ($imageUrl) {
            $data['image_url'] = $imageUrl;
        }

        $this->db->update('products', $data, ['id' => $id]);
        $this->redirect('admin/inventory');
    }

    /**
     * Toggle product visibility on public page
     */
    public function toggleProductStatus($id)
    {
        $product = $this->db->getOne('products', ['id' => $id]);
        if ($product) {
            $newStatus = $product['is_active'] ? 0 : 1;
            $this->db->update('products', ['is_active' => $newStatus], ['id' => $id]);
        }
        $this->redirect('admin/inventory');
    }

    /**
     * Helper to handle image uploads
     */
    private function handleImageUpload()
    {
        if (!empty($_POST['cropped_image'])) {
            $base64Data = $_POST['cropped_image'];
            if (strpos($base64Data, 'data:image/') === 0) {
                list($type, $data) = explode(';', $base64Data);
                list(, $data)      = explode(',', $data);
                $data = base64_decode($data);

                $uploadDir = __DIR__ . '/../../public/uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $filename = time() . '_cropped.png';
                if (file_put_contents($uploadDir . $filename, $data)) {
                    return $this->baseUrl . 'public/uploads/products/' . $filename;
                }
            }
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['image']['tmp_name'];
            $filename = time() . '_' . $_FILES['image']['name'];
            $uploadDir = __DIR__ . '/../../public/uploads/products/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                return $this->baseUrl . 'public/uploads/products/' . $filename;
            }
        }
        return null;
    }

    /**
     * View Orders with Pagination
     */
    public function orders()
    {
        $currentPage = $_GET['page'] ?? 1;
        $itemsPerPage = 10;
        $totalItems = $this->db->countAll('orders');

        $pager = $this->getPaginationData($totalItems, $itemsPerPage, $currentPage);

        $orders = $this->db->getAll('orders', [], 'created_at DESC', $pager['limit'], $pager['offset']);

        $this->showView('admin/orders/index.twig', [
            'title' => 'Pedidos Recibidos',
            'orders' => $orders,
            'pager' => $pager
        ]);
    }

    /**
     * Show create order form
     */
    public function createOrder()
    {
        $products = $this->db->getAll('products');
        $this->showView('admin/orders/edit_order.twig', [
            'title' => 'Nuevo Pedido',
            'products' => $products
        ]);
    }

    /**
     * Store new order
     */
    public function storeOrder()
    {
        $orderData = [
            'customer_first_name' => $_POST['customer_first_name'],
            'customer_last_name'  => $_POST['customer_last_name'],
            'customer_email'      => $_POST['customer_email'],
            'customer_phone'      => $_POST['customer_phone'],
            'total_amount'        => $_POST['total_amount'],
            'status'              => 'pending',
            'notes'               => $_POST['notes'] ?? '',
            'tracking_token'      => bin2hex(random_bytes(16))
        ];

        $this->db->insert('orders', $orderData);
        $orderId = $this->db->lastInsertId();

        // Handle items if provided (simplified for now)
        if (isset($_POST['products']) && is_array($_POST['products'])) {
            foreach ($_POST['products'] as $item) {
                $this->db->insert('order_items', [
                    'order_id'   => $orderId,
                    'product_id' => $item['id'],
                    'quantity'   => $item['quantity'],
                    'price_at_time' => $item['price']
                ]);
            }
        }

        $this->redirect('admin/orders');
    }

    /**
     * Show edit order form
     */
    public function editOrder($id)
    {
        $order = $this->db->getOne('orders', ['id' => $id]);
        $items = $this->db->getJoin('order_items', 'order_items.*, products.name as product_name', [
            ['table' => 'products', 'on' => 'order_items.product_id = products.id']
        ], ['order_id' => $id]);

        $this->showView('admin/orders/edit_order.twig', [
            'title' => 'Editar Pedido',
            'order' => $order,
            'items' => $items
        ]);
    }

    /**
     * Update order
     */
    public function updateOrder($id)
    {
        $data = [
            'status' => $_POST['status'],
            'customer_first_name' => $_POST['customer_first_name'],
            'customer_last_name' => $_POST['customer_last_name'],
            'customer_email' => $_POST['customer_email'],
            'customer_phone' => $_POST['customer_phone']
        ];

        $this->db->update('orders', $data, ['id' => $id]);
        $this->redirect('admin/orders');
    }

    /**
     * Quotations management
     */
    public function quotations()
    {
        $quotations = $this->db->getAll('quotations', [], 'sent_at DESC');
        $this->showView('admin/quotations/index.twig', [
            'title' => 'Cotizaciones Enviadas',
            'quotations' => $quotations
        ]);
    }

    /**
     * Show create quotation form
     */
    public function createQuotation()
    {
        $this->showView('admin/quotations/create.twig', [
            'title' => 'Crear Presupuesto'
        ]);
    }

    /**
     * Edit an existing quotation (useful for processing drafts from the web)
     */
    public function editQuotation($id)
    {
        $quotation = $this->db->getOne('quotations', ['id' => $id]);
        $this->showView('admin/quotations/create.twig', [
            'title' => 'Completar Petición de Cotización',
            'quotation' => $quotation
        ]);
    }

    /**
     * Store quotation and send PDF email
     */
    public function storeQuotation()
    {
        $id = $_POST['id'] ?? null;

        $trackingToken = md5(uniqid(rand(), true));
        $status = $_POST['status'] ?? 'sent';

        if ($id) {
            $existingQuote = $this->db->getOne('quotations', ['id' => $id]);
            if ($existingQuote && !empty($existingQuote['tracking_token'])) {
                $trackingToken = $existingQuote['tracking_token'];
            }
        }

        $data = [
            'customer_first_name' => $_POST['customer_first_name'] ?? '',
            'customer_last_name'  => $_POST['customer_last_name'] ?? '',
            'customer_email'      => $_POST['customer_email'] ?? '',
            'subject'             => $_POST['subject'] ?? '',
            'content'             => $_POST['content'] ?? '',
            'admin_response'      => $_POST['admin_response'] ?? '',
            'status'              => $status,
            'sent_at'             => date('Y-m-d H:i:s'),
            'tracking_token'      => $trackingToken
        ];

        // 1. Generate PDF
        $business = $this->db->getOne('business_settings', ['id' => 1]);
        if (!empty($business['logo_url'])) {
            $business['logo_url'] = $this->imageToBase64($business['logo_url']);
        }
        $pdfService = new PdfService();
        $html = $this->renderTemplate('admin/quotations/pdf_template.twig', array_merge($data, ['business' => $business]));

        $filename = 'cotizacion_' . time() . '.pdf';
        $savePath = __DIR__ . '/../../public/storage/quotations/' . $filename;

        $pdfPath = null;
        if ($pdfService->save($html, $savePath)) {
            $data['pdf_path'] = $filename;
            $pdfPath = $savePath;
        }

        // 2. Save to DB
        if ($id) {
            $this->db->update('quotations', $data, ['id' => $id]);
        } else {
            $this->db->insert('quotations', $data);
        }

        // 3. Send Email
        $attachPdf = isset($_POST['attach_pdf']) && $_POST['attach_pdf'] == '1';
        $emailService = new EmailService();
        $customerName = $data['customer_first_name'] . ' ' . $data['customer_last_name'];

        // Prepare email body with HTML line breaks
        $emailBody = "Hola <b>{$customerName}</b>,<br><br>";
        $emailBody .= nl2br($data['admin_response'] ?: $data['content']);
        $emailBody .= "<br><br><a href='http://localhost/momomy_bakery/quotation/{$trackingToken}' style='display:inline-block; padding:10px 20px; background:#f0427c; color:#fff; text-decoration:none; border-radius:10px; font-weight:bold;'>Ver Cotización en Línea</a><br><br>";
        $emailBody .= "Atentamente,<br><b>" . ($business['business_name'] ?? 'Momomy Bakery') . "</b>";

        $emailService->send(
            $data['customer_email'],
            $data['subject'],
            $emailBody,
            $attachPdf ? $pdfPath : null
        );

        // 4. Create customer catalog entry if status is accepted
        if ($status === 'accepted') {
            $existingCustomer = $this->db->getOne('customers', ['email' => $data['customer_email']]);
            if (!$existingCustomer) {
                $rawPass = 'Tempo' . rand(100, 999) . '!';
                $this->db->insert('customers', [
                    'first_name' => $data['customer_first_name'],
                    'last_name'  => $data['customer_last_name'],
                    'email'      => $data['customer_email'],
                    'password'   => password_hash($rawPass, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                $accountBody = "Hola <b>{$customerName}</b>,<br><br>";
                $accountBody .= "Ya que tu cotización <b>'{$data['subject']}'</b> ha sido confirmada/aceptada, hemos creado automáticamente tu cuenta en nuestro portal de clientes oficial.<br><br>";
                $accountBody .= "Ingresa al portal para tener el historial de tus compras y darle seguimiento a tus pedidos:<br><br>";
                $accountBody .= "<a href='http://localhost/momomy_bakery/connect/login'><b>Acceder a Mi Portal</b></a><br><br>";
                $accountBody .= "<b>Tu usuario:</b> {$data['customer_email']}<br>";
                $accountBody .= "<b>Tu contraseña temporal:</b> {$rawPass}<br><br>";
                $accountBody .= "Te sugerimos iniciar sesión para cambiar esta contraseña temporal por tu propia seguridad.<br><br>";
                $accountBody .= "Atentamente,<br><b>" . ($business['business_name'] ?? 'Momomy Bakery') . "</b>";

                $emailService->send(
                    $data['customer_email'],
                    "¡Bienvenido(a) a " . ($business['business_name'] ?? 'Momomy Bakery') . "! (Credenciales de Acceso)",
                    $accountBody
                );
            }
        }

        $this->redirect('admin/quotations');
    }

    /**
     * Download or view generated PDF
     */
    public function downloadQuotation($filename)
    {
        $path = __DIR__ . '/../../public/storage/quotations/' . $filename;
        if (file_exists($path)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            readfile($path);
            exit;
        } else {
            echo "El archivo no existe.";
        }
    }

    /**
     * Generate Sales Note (Receipt) for an order
     */
    public function generateReceipt($orderId)
    {
        $order = $this->db->getOne('orders', ['id' => $orderId]);
        if (!$order) {
            echo "Pedido no encontrado.";
            return;
        }

        $items = $this->db->getJoin('order_items', 'order_items.*, products.name as product_name', [
            ['table' => 'products', 'on' => 'order_items.product_id = products.id']
        ], ['order_id' => $orderId]);

        $pdfService = new PdfService();
        $business = $this->db->getOne('business_settings', ['id' => 1]);
        if (!empty($business['logo_url'])) {
            $business['logo_url'] = $this->imageToBase64($business['logo_url']);
        }
        $trackingUrl = $this->baseUrl . 'connect/order/' . ($order['tracking_token'] ?? $order['id']);

        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($trackingUrl);
        $qrCodeBase64 = $this->imageToBase64($qrCodeUrl);

        $html = $this->renderTemplate('admin/orders/receipt_template.twig', [
            'order' => $order,
            'items' => $items,
            'business' => $business,
            'tracking_url' => $trackingUrl,
            'qr_code_base64' => $qrCodeBase64
        ]);

        $pdfService->stream($html, 'nota_venta_' . $orderId . '.pdf');
    }

    /**
     * List all users
     */
    public function users()
    {
        $users = $this->db->getJoin('users', 'users.*, roles.name as role_name', [
            ['table' => 'roles', 'on' => 'users.role_id = roles.id']
        ], [], 'created_at DESC');

        $this->showView('admin/users/index.twig', [
            'title' => 'Gestión de Usuarios',
            'users' => $users
        ]);
    }

    /**
     * Show create user form
     */
    public function createUser()
    {
        $roles = $this->db->getAll('roles');
        $this->showView('admin/users/edit.twig', [
            'title' => 'Nuevo Usuario',
            'roles' => $roles
        ]);
    }

    /**
     * Store a new user
     */
    public function storeUser()
    {
        $avatarUrl = $this->handleFileWithDir($_FILES['avatar'] ?? null, 'users');

        $data = [
            'first_name' => $_POST['first_name'],
            'last_name'  => $_POST['last_name'],
            'email'      => $_POST['email'],
            'password'   => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role_id'    => $_POST['role_id'],
            'avatar_url' => $avatarUrl
        ];

        $this->db->insert('users', $data);
        $this->redirect('admin/users');
    }

    /**
     * User profile settings
     */
    public function profile()
    {
        $user = $this->db->getJoin('users', 'users.*, roles.name as role_name', [
            ['table' => 'roles', 'on' => 'users.role_id = roles.id']
        ], ['users.id' => $_SESSION['user_id']]);

        $this->showView('admin/profile.twig', [
            'title' => 'Mi Perfil',
            'user' => $user[0] ?? null
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile()
    {
        $data = [
            'first_name' => $_POST['first_name'],
            'last_name'  => $_POST['last_name'],
            'email'      => $_POST['email'],
            'is_2fa_enabled' => isset($_POST['is_2fa_enabled']) ? 1 : 0
        ];

        $avatarUrl = $this->handleFileWithDir($_FILES['avatar'] ?? null, 'users');
        if ($avatarUrl) {
            $data['avatar_url'] = $avatarUrl;
            $_SESSION['user_avatar'] = $avatarUrl;
        }

        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $this->db->update('users', $data, ['id' => $_SESSION['user_id']]);
        $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];

        $this->redirect('admin/profile');
    }

    /**
     * Show Customers list
     */
    public function customers()
    {
        $customers = $this->db->getAll('customers', [], 'created_at DESC');
        $this->showView('admin/customers/index.twig', [
            'title' => 'Catálogo de Clientes',
            'customers' => $customers
        ]);
    }

    /**
     * Edit Customer 
     */
    public function editCustomer($id)
    {
        $customer = $this->db->getOne('customers', ['id' => $id]);
        if (!$customer) {
            $this->redirect('admin/customers');
        }

        $this->showView('admin/customers/edit.twig', [
            'title' => 'Editar Cliente',
            'customer' => $customer
        ]);
    }

    /**
     * Update Customer
     */
    public function updateCustomer($id)
    {
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name'  => $_POST['last_name'] ?? '',
            'email'      => $_POST['email'] ?? '',
            'phone'      => $_POST['phone'] ?? ''
        ];

        // Also update password if provided
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $this->db->update('customers', $data, ['id' => $id]);
        $this->redirect('admin/customers');
    }

    /**
     * Delete Customer
     */
    public function deleteCustomer($id)
    {
        // Opt: check for dependencies like orders/quotations before deleting. But let's keep it simple.
        $this->db->delete('customers', ['id' => $id]);
        $this->redirect('admin/customers');
    }

    /**
     * Business settings
     */
    public function settings()
    {
        $business = $this->db->getOne('business_settings', ['id' => 1]);
        $this->showView('admin/settings.twig', [
            'title' => 'Configuración del Negocio',
            'business' => $business
        ]);
    }

    /**
     * Update business settings
     */
    public function updateSettings()
    {
        $data = [
            'business_name' => $_POST['business_name'],
            'address'       => $_POST['address'],
            'email'         => $_POST['email'],
            'phone'         => $_POST['phone'],
            'tax_id'        => $_POST['tax_id']
        ];

        $logoUrl = $this->handleFileWithDir($_FILES['logo'] ?? null, 'logo');
        if ($logoUrl) {
            $data['logo_url'] = $logoUrl;
        }

        $this->db->update('business_settings', $data, ['id' => 1]);
        $this->redirect('admin/settings');
    }

    /**
     * Backup Management View
     */
    public function backups()
    {
        $backupDir = __DIR__ . '/../../backups/';
        $files = [];

        if (is_dir($backupDir)) {
            $rawFiles = scandir($backupDir, SCANDIR_SORT_DESCENDING);
            foreach ($rawFiles as $file) {
                if ($file !== '.' && $file !== '..') {
                    $filePath = $backupDir . $file;
                    $files[] = [
                        'name' => $file,
                        'size' => round(filesize($filePath) / 1024, 2) . ' KB',
                        'date' => date('d/m/Y H:i', filemtime($filePath))
                    ];
                }
            }
        }

        $this->showView('admin/backups/index.twig', [
            'title' => 'Respaldos de Seguridad',
            'files' => $files
        ]);
    }

    /**
     * Generate backup and save to disk
     */
    public function generateBackup()
    {
        // Get all tables
        $this->db->query("SHOW TABLES");
        $tables_raw = $this->db->resultSet();
        $tables = [];
        $db_name_key = 'Tables_in_' . DB_NAME;

        foreach ($tables_raw as $row) {
            $tables[] = $row[$db_name_key];
        }

        $sql = "-- Momomy Bakery - Database Backup\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $this->db->query("SHOW CREATE TABLE `{$table}`");
            $createTable = $this->db->single();
            $sql .= $createTable['Create Table'] . ";\n\n";

            $rows = $this->db->getAll($table);
            foreach ($rows as $row) {
                $keys = array_keys($row);
                $values = array_values($row);
                $escapedValues = array_map(function ($v) {
                    if ($v === null) return 'NULL';
                    return "'" . str_replace("'", "''", $v) . "'";
                }, $values);
                $sql .= "INSERT INTO `{$table}` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            $sql .= "\n";
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;";

        $filename = 'backup_momomy_' . date('Y-m-d_His') . '.sql';
        $path = __DIR__ . '/../../backups/' . $filename;

        if (file_put_contents($path, $sql)) {
            $this->redirect('admin/backups');
        } else {
            die("Error al guardar el respaldo. Verifica permisos de la carpeta 'backups'.");
        }
    }

    /**
     * Download a backup file
     */
    public function downloadBackupFile($filename)
    {
        $path = __DIR__ . '/../../backups/' . $filename;
        if (file_exists($path)) {
            header('Content-Type: application/sql');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            readfile($path);
            exit;
        }
        $this->redirect('admin/backups');
    }

    /**
     * Delete a backup file
     */
    public function deleteBackupFile($filename)
    {
        $path = __DIR__ . '/../../backups/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
        $this->redirect('admin/backups');
    }

    /**
     * Delete product
     */
    public function deleteProduct($id)
    {
        $this->db->delete('products', ['id' => $id]);
        $this->redirect('admin/inventory');
    }

    /**
     * Delete order
     */
    public function deleteOrder($id)
    {
        // Delete items first
        $this->db->delete('order_items', ['order_id' => $id]);
        $this->db->delete('orders', ['id' => $id]);
        $this->redirect('admin/orders');
    }

    /**
     * Delete quotation
     */
    public function deleteQuotationEntry($id)
    {
        $this->db->delete('quotations', ['id' => $id]);
        $this->redirect('admin/quotations');
    }

    /**
     * Category Management
     */
    public function categories()
    {
        $categories = $this->db->getAll('categories');
        $this->showView('admin/inventory/categories.twig', [
            'title' => 'Gestionar Categorías',
            'categories' => $categories
        ]);
    }

    public function storeCategory()
    {
        $name = $_POST['name'] ?? '';
        if ($name) {
            $this->db->insert('categories', ['name' => $name]);
        }
        $this->redirect('admin/categories');
    }

    public function deleteCategory($id)
    {
        $this->db->delete('categories', ['id' => $id]);
        $this->redirect('admin/categories');
    }

    /**
     * News / Latest Updates Management
     */
    /**
     * News management with Pagination
     */
    public function news()
    {
        $currentPage = $_GET['page'] ?? 1;
        $itemsPerPage = 5;
        $totalItems = $this->db->countAll('news');

        $pager = $this->getPaginationData($totalItems, $itemsPerPage, $currentPage);

        $news = $this->db->getAll('news', [], 'created_at DESC', $pager['limit'], $pager['offset']);
        foreach ($news as &$item) {
            $item['image_url'] = $this->normalizeImageUrl($item['image_url']);
        }

        $this->showView('admin/news/index.twig', [
            'title' => 'Noticias y Novedades',
            'news' => $news,
            'pager' => $pager
        ]);
    }

    public function createNews()
    {
        $this->showView('admin/news/edit.twig', [
            'title' => 'Nueva Noticia'
        ]);
    }

    public function storeNews()
    {
        $imageUrl = $this->handleFileWithDir($_FILES['image'] ?? null, 'news');
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        $data = [
            'title'        => $_POST['title'],
            'content'      => $_POST['content'],
            'image_url'    => $imageUrl,
            'is_published' => $isPublished
        ];

        $this->db->insert('news', $data);
        $newsId = $this->db->lastInsertId();

        // If published, notify subscribers
        if ($isPublished) {
            $subscribers = $this->db->getAll('newsletter_subscribers');
            if (!empty($subscribers)) {
                $emailService = new EmailService();
                $subject = "✨ Nueva Noticia en Momomy Bakery: " . $data['title'];

                // Simple HTML body
                $body = "<h2>{$data['title']}</h2>";
                $body .= "<p>" . nl2br($data['content']) . "</p>";
                $body .= "<br><a href='{$this->baseUrl}#news' style='background:#f0427c; color:white; padding:10px 20px; border-radius:10px; text-decoration:none;'>Ver más detalles</a>";

                foreach ($subscribers as $sub) {
                    $emailService->send($sub['email'], $subject, $body);
                }
            }
        }

        $this->redirect('admin/news');
    }

    public function editNews($id)
    {
        $item = $this->db->getOne('news', ['id' => $id]);
        $this->showView('admin/news/edit.twig', [
            'title' => 'Editar Noticia',
            'news' => $item
        ]);
    }

    public function updateNews($id)
    {
        $data = [
            'title'        => $_POST['title'],
            'content'      => $_POST['content'],
            'is_published' => isset($_POST['is_published']) ? 1 : 0
        ];

        $imageUrl = $this->handleFileWithDir($_FILES['image'] ?? null, 'news');
        if ($imageUrl) {
            $data['image_url'] = $imageUrl;
        }

        $this->db->update('news', $data, ['id' => $id]);
        $this->redirect('admin/news');
    }

    public function deleteNews($id)
    {
        $this->db->delete('news', ['id' => $id]);
        $this->redirect('admin/news');
    }

    /**
     * Newsletter Subscribers Management
     */
    public function subscribers()
    {
        $subscribers = $this->db->getAll('newsletter_subscribers', [], 'subscribed_at DESC');
        $this->showView('admin/news/subscribers.twig', [
            'title' => 'Suscriptores al Newsletter',
            'subscribers' => $subscribers
        ]);
    }

    /**
     * Contact messages management
     */
    public function contacts()
    {
        $contacts = $this->db->getAll('contacts', [], 'created_at DESC');
        $this->showView('admin/contacts/index.twig', [
            'title' => 'Mensajes de Contacto',
            'contacts' => $contacts
        ]);
    }

    /**
     * Mark message as read
     */
    public function markAsRead($id)
    {
        $this->db->update('contacts', ['is_read' => 1], ['id' => $id]);
        $this->redirect('admin/contacts');
    }

    /**
     * Delete contact message
     */
    public function deleteContact($id)
    {
        $this->db->delete('contacts', ['id' => $id]);
        $this->redirect('admin/contacts');
    }

    /**
     * Generic file handler
     */
    private function handleFileWithDir($file, $subdir)
    {
        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $file['tmp_name'];
            $filename = time() . '_' . $file['name'];
            $uploadDir = __DIR__ . '/../../public/uploads/' . $subdir . '/';

            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                return $this->baseUrl . 'public/uploads/' . $subdir . '/' . $filename;
            }
        }
        return null;
    }

    /**
     * Helper to convert an image URL to a local absolute path or Base64 URI.
     * Required to avoid timeouts with php's single threaded dev server and dompdf.
     */
    private function imageToBase64($url)
    {
        if (empty($url)) return null;

        $localPath = str_replace($this->baseUrl, realpath(__DIR__ . '/../../') . '/', $url);
        $localPath = str_replace('/', DIRECTORY_SEPARATOR, $localPath);

        if (file_exists($localPath)) {
            $data = file_get_contents($localPath);
            $type = pathinfo($localPath, PATHINFO_EXTENSION);
            if (strtolower($type) == 'jpg') $type = 'jpeg';
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        try {
            $context = stream_context_create(['http' => ['timeout' => 5]]);
            $data = @file_get_contents($url, false, $context);
            if ($data) {
                // If it's a QR code, assuming PNG
                return 'data:image/png;base64,' . base64_encode($data);
            }
        } catch (\Exception $e) {
        }

        return $url;
    }
}
