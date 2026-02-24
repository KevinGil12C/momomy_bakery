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
     * Inventory management
     */
    public function inventory()
    {
        $products = $this->db->getJoin('products', 'products.*, categories.name as category', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ]);

        $this->showView('admin/inventory/index.twig', [
            'title' => 'Gestión de Inventario',
            'products' => $products
        ]);
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
            'category_id' => $_POST['category_id'],
            'name'        => $_POST['name'],
            'description' => $_POST['description'],
            'price'       => $_POST['price'],
            'stock'       => $_POST['stock'],
            'image_url'   => $imageUrl
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
            'category_id' => $_POST['category_id'],
            'name'        => $_POST['name'],
            'description' => $_POST['description'],
            'price'       => $_POST['price'],
            'stock'       => $_POST['stock']
        ];

        $imageUrl = $this->handleImageUpload();
        if ($imageUrl) {
            $data['image_url'] = $imageUrl;
        }

        $this->db->update('products', $data, ['id' => $id]);
        $this->redirect('admin/inventory');
    }

    /**
     * Helper to handle image uploads
     */
    private function handleImageUpload()
    {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['image']['tmp_name'];
            $filename = time() . '_' . $_FILES['image']['name'];
            $uploadDir = __DIR__ . '/../../public/uploads/products/';

            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                return $this->baseUrl . 'public/uploads/products/' . $filename;
            }
        }
        return null;
    }

    /**
     * View Orders
     */
    public function orders()
    {
        $orders = $this->db->getAll('orders', [], 'created_at DESC');
        $this->showView('admin/orders/index.twig', [
            'title' => 'Pedidos Recibidos',
            'orders' => $orders
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
            'status'              => $_POST['status'] ?? 'pending',
            'total_amount'        => $_POST['total_amount'] ?? 0
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
        $data = [
            'customer_first_name' => $_POST['customer_first_name'] ?? '',
            'customer_last_name'  => $_POST['customer_last_name'] ?? '',
            'customer_email'      => $_POST['customer_email'] ?? '',
            'subject'             => $_POST['subject'] ?? '',
            'content'             => $_POST['content'] ?? '',
            'status'              => 'sent',
            'sent_at'             => date('Y-m-d H:i:s')
        ];

        // 1. Generate PDF
        $business = $this->db->getOne('business_settings', ['id' => 1]);
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
        $emailBody .= nl2br($data['content']);
        $emailBody .= "<br><br>Atentamente,<br><b>" . ($business['business_name'] ?? 'Momomy Bakery') . "</b>";

        $emailService->send(
            $data['customer_email'],
            $data['subject'],
            $emailBody,
            $attachPdf ? $pdfPath : null
        );

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
        $trackingUrl = $this->baseUrl . 'order/status/' . $orderId;

        $html = $this->renderTemplate('admin/orders/receipt_template.twig', [
            'order' => $order,
            'items' => $items,
            'business' => $business,
            'tracking_url' => $trackingUrl
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
            'email'      => $_POST['email']
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
}
