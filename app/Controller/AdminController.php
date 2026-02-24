<?php

namespace App\Controller;

use App\Models\Database;

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
    }

    /**
     * Main Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_products' => $this->db->countAll('products'),
            'total_orders' => $this->db->countAll('orders'),
            'pending_orders' => count($this->db->select('orders', ['status' => 'pending'])),
            'unread_messages' => count($this->db->select('contacts', ['is_read' => 0]))
        ];

        $this->showView('admin/dashboard.twig', [
            'title' => 'Dashboard Momomy',
            'stats' => $stats
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
}
