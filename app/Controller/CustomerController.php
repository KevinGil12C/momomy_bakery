<?php

namespace App\Controller;

use App\Models\Database;

class CustomerController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
    }

    /**
     * Guest view for order status via QR/Token
     */
    public function viewOrder($token)
    {
        $order = $this->db->getOne('orders', ['tracking_token' => $token]);
        if (!$order) {
            die("Enlace de seguimiento no válido.");
        }

        $items = $this->db->getJoin('order_items', 'order_items.*, products.name as product_name', [
            ['table' => 'products', 'on' => 'order_items.product_id = products.id']
        ], ['order_id' => $order['id']]);

        $business = $this->db->getOne('business_settings', ['id' => 1]);

        $this->showView('public/order_status.twig', [
            'title'    => 'Seguimiento de Mi Pedido',
            'order'    => $order,
            'items'    => $items,
            'business' => $business,
            'is_guest' => true
        ]);
    }

    /**
     * Show registration form (optionally linked to an order)
     */
    public function showRegister()
    {
        $token = $_GET['claim'] ?? null;
        $order = null;
        if ($token) {
            $order = $this->db->getOne('orders', ['tracking_token' => $token]);
        }

        $this->showView('public/connect/register.twig', [
            'title' => 'Regístrate en Momomy',
            'order' => $order
        ]);
    }

    /**
     * Handle registration
     */
    public function register()
    {
        $email = $_POST['email'];
        $exists = $this->db->getOne('customers', ['email' => $email]);
        if ($exists) {
            // Error handling
            header("Location: " . $this->baseUrl . "connect/register?error=email_exists");
            exit;
        }

        $customerId = $this->db->insert('customers', [
            'first_name' => $_POST['first_name'],
            'last_name'  => $_POST['last_name'],
            'email'      => $email,
            'password'   => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'phone'      => $_POST['phone']
        ]);

        // If claiming an order
        if (!empty($_POST['claim_token'])) {
            $this->db->update('orders', ['customer_id' => $customerId], ['tracking_token' => $_POST['claim_token']]);
        }

        $_SESSION['customer_id'] = $customerId;
        $_SESSION['customer_name'] = $_POST['first_name'];

        $this->redirect('connect/dashboard');
    }

    /**
     * Customer Dashboard
     */
    public function dashboard()
    {
        if (!isset($_SESSION['customer_id'])) $this->redirect('connect/login');

        $customerId = $_SESSION['customer_id'];
        $orders = $this->db->getAll('orders', ['customer_id' => $customerId], 'created_at DESC');
        $quotations = $this->db->getAll('quotations', ['customer_email' => $this->getCustomerEmail($customerId)], 'sent_at DESC');

        $this->showView('public/connect/dashboard.twig', [
            'title' => 'Mi Portal Momomy',
            'orders' => $orders,
            'quotations' => $quotations
        ]);
    }

    /**
     * Show login form
     */
    public function showLogin()
    {
        $this->showView('public/connect/login.twig', [
            'title' => 'Inicia Sesión en Momomy'
        ]);
    }

    /**
     * Handle customer login
     */
    public function login()
    {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $customer = $this->db->getOne('customers', ['email' => $email]);
        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['first_name'];
            $this->redirect('connect/dashboard');
        } else {
            header("Location: " . $this->baseUrl . "connect/login?error=invalid_credentials");
            exit;
        }
    }

    /**
     * Logout customer
     */
    public function logout()
    {
        unset($_SESSION['customer_id']);
        unset($_SESSION['customer_name']);
        $this->redirect('connect/login');
    }

    private function getCustomerEmail($id)
    {
        $c = $this->db->getOne('customers', ['id' => $id]);
        return $c['email'] ?? '';
    }
}
