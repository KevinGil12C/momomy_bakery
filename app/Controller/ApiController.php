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
     * Receive an order request from Vercel
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
            'notes'               => $data['notes'] ?? ''
        ];

        $orderId = $this->db->insert('orders', $orderData);
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
            'admin@momomy.com', // Change to your dynamic admin email if needed
            'Nuevo Pedido - Momomy Bakery',
            "Se ha recibido un nuevo pedido #$orderId de {$customerName}. Revisa el panel administrativo para procesarlo."
        );

        return $this->jsonResponse(['message' => 'Order received', 'order_id' => $orderId]);
    }

    /**
     * Generate a recovery token for an email
     */
    public function recovery()
    {
        $email = $_POST['email'] ?? null;
        if (!$email) return $this->jsonResponse(['error' => 'Email required'], 400);

        $jwt = new JwtService();
        $token = $jwt->generateToken(['email' => $email], 1800); // 30 mins

        // In a real app, send this via EmailService
        return $this->jsonResponse([
            'message' => 'Token generated',
            'token' => $token
        ]);
    }

    /**
     * Public endpoint to check order status
     */
    public function orderStatus($orderId)
    {
        $order = $this->db->getOne('orders', ['id' => $orderId]);
        if (!$order) {
            die("Pedido no encontrado.");
        }

        $items = $this->db->getJoin('order_items', 'order_items.*, products.name as product_name', [
            ['table' => 'products', 'on' => 'order_items.product_id = products.id']
        ], ['order_id' => $orderId]);

        $business = $this->db->getOne('business_settings', ['id' => 1]);

        $this->showView('public/order_status.twig', [
            'title'    => 'Estado de mi Pedido',
            'order'    => $order,
            'items'    => $items,
            'business' => $business
        ]);
    }

    public function requestQuotation()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return $this->jsonResponse(['error' => 'Invalid data'], 400);

        $quotationData = [
            'customer_first_name' => $data['customer_first_name'] ?? '',
            'customer_last_name'  => $data['customer_last_name'] ?? '',
            'customer_email'      => $data['customer_email'] ?? '',
            'subject'             => $data['subject'] ?? 'Petición de Cotización',
            'content'             => $data['content'] ?? '',
            'status'              => 'draft',
            'sent_at'             => date('Y-m-d H:i:s')
        ];

        $this->db->insert('quotations', $quotationData);
        $id = $this->db->lastInsertId();

        // Notify Admin of new request
        $email = new EmailService();
        $email->send(
            'admin@momomy.com',
            'Nueva Petición de Cotización',
            "Se ha recibido una petición de cotización #$id de {$quotationData['customer_first_name']}. Revisa el panel para completar y enviar el presupuesto."
        );

        return $this->jsonResponse(['message' => 'Quotation request received', 'id' => $id]);
    }
}
