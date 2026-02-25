<?php

namespace App\Controller;

use App\Models\Database;

class PublicController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
    }

    /**
     * Helper to get average rating for a set of products
     */
    private function attachRatings($products)
    {
        if (empty($products)) return [];

        $wasArray = true;
        if (!isset($products[0])) {
            $products = [$products];
            $wasArray = false;
        }

        foreach ($products as &$p) {
            $sql = "SELECT AVG(rating) as average, COUNT(*) as total FROM product_comments WHERE product_id = :id";
            $res = $this->db->rawQuery($sql, [':id' => $p['id']]);
            $p['avg_rating'] = $res ? round($res[0]['average'] ?? 0, 1) : 0;
            $p['total_reviews'] = $res ? ($res[0]['total'] ?? 0) : 0;

            // Normalize image path
            $p['image_url'] = $this->normalizeImageUrl($p['image_url']);
        }

        return $wasArray ? $products : $products[0];
    }

    /**
     * Landing Page / Home
     */
    public function index()
    {
        // Fetch specialties (featured products)
        $specialties = $this->db->getJoin('products', 'products.*, categories.name as category_name', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ], ['is_active' => 1, 'is_specialty' => 1], 'id DESC', 4);

        // Fetch special of the week (banner)
        $specialOfWeek = $this->db->getJoin('products', '*', [], ['is_active' => 1, 'is_special_of_week' => 1], 'id DESC', 1);

        // Fetch latest news
        $news = $this->db->getAll('news', ['is_published' => 1], 'created_at DESC', 3);

        $this->showView('public/home.twig', [
            'title' => 'Inicio',
            'specialties' => $this->attachRatings($specialties),
            'special_of_week' => $specialOfWeek ? $this->attachRatings($specialOfWeek[0]) : null,
            'news' => $news
        ]);
    }

    /**
     * Main Catalog View
     */
    public function catalog()
    {
        $currentPage = $_GET['page'] ?? 1;
        $itemsPerPage = 12;
        $totalItems = $this->db->countAll('products');

        $pager = $this->getPaginationData($totalItems, $itemsPerPage, $currentPage);

        $categories = $this->db->getAll('categories');
        $products = $this->db->getJoin('products', 'products.*, categories.name as category_name', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ], ['is_active' => 1], 'id DESC', $pager['limit'], $pager['offset']);

        // Fetch special of the week for sidebar
        $specialOfWeekRaw = $this->db->getJoin('products', '*', [], ['is_active' => 1, 'is_special_of_week' => 1], 'id DESC', 1);
        $specialOfWeek = $specialOfWeekRaw ? $this->attachRatings($specialOfWeekRaw) : null;

        $this->showView('public/catalog.twig', [
            'title' => 'Catálogo',
            'categories' => $categories,
            'products' => $this->attachRatings($products),
            'special_of_week' => $specialOfWeek ? $specialOfWeek[0] : null,
            'pager' => $pager
        ]);
    }

    /**
     * Single Product View (Amazon Style)
     */
    public function product($id)
    {
        $product = $this->db->getJoin('products', 'products.*, categories.name as category_name', [
            ['table' => 'categories', 'on' => 'products.category_id = categories.id']
        ], ['products.id' => $id]);

        if (!$product) {
            $this->redirect('catalog');
        }

        $comments = $this->db->getJoin('product_comments', 'product_comments.*, customers.first_name, customers.last_name', [
            ['table' => 'customers', 'on' => 'product_comments.user_id = customers.id']
        ], ['product_id' => $id], 'created_at DESC');

        $this->showView('public/product.twig', [
            'title' => $product[0]['name'],
            'product' => $this->attachRatings($product[0]),
            'comments' => $comments
        ]);
    }

    /**
     * Contact Page
     */
    public function contact()
    {
        $this->showView('public/contact.twig', [
            'title' => 'Contáctanos'
        ]);
    }

    /**
     * Quotation Request Page
     */
    public function quotation()
    {
        $this->showView('public/quotation.twig', [
            'title' => 'Solicitar Cotización'
        ]);
    }

    /**
     * Privacy Policy Page
     */
    public function privacy()
    {
        $this->showView('public/legal/privacy.twig', [
            'title' => 'Aviso de Privacidad'
        ]);
    }

    /**
     * Terms and Conditions Page
     */
    public function terms()
    {
        $this->showView('public/legal/terms.twig', [
            'title' => 'Términos y Condiciones'
        ]);
    }
}
