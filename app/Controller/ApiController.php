<?php

namespace App\Controller;

use App\Services\EmailService;
use App\Services\JwtService;
use App\Models\Database;

class ApiController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
    }

    /**
     * Get all active products for the public site
     */
    public function products()
    {
        $products = $this->db->getJoin('products', 'products.*, categories.name as category', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ], ['is_active' => 1]);

        return $this->jsonResponse($products);
    }

    /**
     * Endpoint to receive a contact request from External Front (Vercel)
     */
    public function receiveContact()
    {
        // For External API, we check for a custom Token or JWT instead of CSRF
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            return $this->jsonResponse(['error' => 'Invalid data'], 400);
        }

        // 1. Save to Database
        $this->db->insert('contacts', [
            'name'    => $data['name'] ?? 'Anónimo',
            'email'   => $data['email'] ?? '',
            'message' => $data['message'] ?? '',
            'is_read' => 0
        ]);

        // 2. Notify Admin via Email
        $email = new EmailService();
        $success = $email->send(
            'admin@momomy.com',
            'Nuevo Contacto desde Vercel',
            "Nombre: {$data['name']}<br>Email: {$data['email']}<br>Mensaje: {$data['message']}"
        );

        return $this->jsonResponse(['message' => 'Message received and saved']);
    }

    /**
     * Process an order submitted from the public front-end
     */
    public function receiveOrder()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return $this->jsonResponse(['error' => 'Invalid data'], 400);

        // 1. Create Order
        $fullName = $data['customer_name'] ?? '';
        $parts = explode(' ', $fullName, 2);

        $orderData = [
            'customer_first_name' => $data['customer_first_name'] ?? ($parts[0] ?? ''),
            'customer_last_name'  => $data['customer_last_name'] ?? ($parts[1] ?? ''),
            'customer_email'      => $data['customer_email'] ?? '',
            'customer_phone'      => $data['customer_phone'] ?? '',
            'total_amount'        => $data['total_amount'] ?? 0,
            'status'              => 'pending',
            'notes'               => $data['notes'] ?? '',
            'tracking_token'      => bin2hex(random_bytes(16))
        ];

        $this->db->insert('orders', $orderData);
        $orderId = $this->db->lastInsertId();

        // 2. Insert Items
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->db->insert('order_items', [
                    'order_id'       => $orderId,
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price_at_time'  => $item['price']
                ]);
            }
        }

        // 3. Notify Admin
        $email = new EmailService();
        $customerName = $orderData['customer_first_name'] . ' ' . $orderData['customer_last_name'];
        $email->send(
            'admin@momomy.com',
            'Nuevo Pedido - Momomy Bakery',
            "Se ha recibido un nuevo pedido #$orderId de {$customerName}. Revisa el panel administrativo para procesarlo."
        );

        return $this->jsonResponse(['message' => 'Order received', 'order_id' => $orderId]);
    }

    /**
     * Submit a contact request (local)
     */
    public function contact()
    {
        $this->db->insert('contacts', [
            'name'    => $_POST['name'],
            'email'   => $_POST['email'],
            'message' => $_POST['message']
        ]);
        return $this->jsonResponse(['success' => true]);
    }

    /**
     * Submit a quotation request (local)
     */
    public function requestQuotation()
    {
        // Check if it's JSON or FormData
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        // If not JSON, use $_POST
        if (!$data) {
            $data = $_POST;
        }

        if (!$data) return $this->jsonResponse(['error' => 'No data received'], 400);

        // Handle Reference Image
        $imageUrl = null;
        if (isset($_FILES['reference_image']) && $_FILES['reference_image']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['reference_image']['tmp_name'];
            $filename = 'quote_' . time() . '_' . $_FILES['reference_image']['name'];
            $uploadDir = __DIR__ . '/../../public/uploads/quotations/';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($tmpPath, $uploadDir . $filename)) {
                $imageUrl = $this->baseUrl . 'public/uploads/quotations/' . $filename;
            }
        }

        $quotationData = [
            'customer_first_name' => $data['customer_first_name'] ?? '',
            'customer_last_name'  => $data['customer_last_name'] ?? '',
            'customer_email'      => $data['customer_email'] ?? '',
            'subject'             => $data['subject'] ?? 'Petición de Cotización',
            'content'             => $data['content'] ?? '',
            'reference_image'     => $imageUrl,
            'status'              => 'draft',
            'sent_at'             => date('Y-m-d H:i:s'),
            'tracking_token'      => md5(uniqid(rand(), true))
        ];

        $this->db->insert('quotations', $quotationData);
        $id = $this->db->lastInsertId();

        // Notify Admin of new request
        $email = new EmailService();
        $email->send(
            'admin@momomy.com',
            'Nueva Cotización con Referencia #' . $id,
            "Se ha recibido una petición de cotización de {$quotationData['customer_first_name']}. <br>" .
                ($imageUrl ? "<b>Nota:</b> El cliente incluyó una imagen de referencia." : "")
        );

        return $this->jsonResponse(['message' => 'Quotation request received', 'id' => $id]);
    }

    public function getProductComments($productId)
    {
        $comments = $this->db->getJoin('product_comments', 'product_comments.*, customers.first_name, customers.last_name', [
            ['table' => 'customers', 'on' => 'product_comments.user_id = customers.id']
        ], ['product_id' => $productId], 'created_at DESC');

        return $this->jsonResponse($comments);
    }

    public function postComment()
    {
        if (!isset($_SESSION['customer_id'])) {
            return $this->jsonResponse(['error' => 'Inicia sesión para comentar'], 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['product_id']) || !isset($data['comment'])) {
            return $this->jsonResponse(['error' => 'Datos inválidos'], 400);
        }

        $commentData = [
            'product_id'  => $data['product_id'],
            'user_id'     => $_SESSION['customer_id'],
            'comment'     => strip_tags($data['comment']),
            'rating'      => (int)($data['rating'] ?? 5)
        ];

        $this->db->insert('product_comments', $commentData);
        return $this->jsonResponse(['message' => 'Comentario publicado']);
    }

    public function subscribeNewsletter()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(['error' => 'Correo inválido'], 400);
        }

        try {
            $this->db->insert('newsletter_subscribers', ['email' => $email]);
            return $this->jsonResponse(['message' => '¡Te has suscrito con éxito! ✨']);
        } catch (\PDOException $e) {
            // Error 23000 is Duplicate Entry in MySQL
            if ($e->getCode() == 23000) {
                return $this->jsonResponse(['message' => 'Ya estás suscrito, ¡gracias! 💖']);
            }
            return $this->jsonResponse(['error' => 'Error al suscribirse'], 500);
        }
    }

    public function getSubscribers()
    {
        // Solo para admin si se requiere, pero aquí devolvemos lista para que el admin la use
        $subscribers = $this->db->getAll('newsletter_subscribers', [], 'created_at DESC');
        return $this->jsonResponse($subscribers);
    }
}
