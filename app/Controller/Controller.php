<?php

namespace App\Controller;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

/**
 * Base Controller
 * Handles core setup including Twig, Session, CSRF, and Timezone
 */
class Controller
{
    protected $twig;
    protected $viewPath;
    protected $baseUrl;

    public function __construct()
    {
        // CORS Headers - Allow Vercel Frontend
        header("Access-Control-Allow-Origin: *"); // For production, replace * with your vercel URL
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // Handle preflight requests
        }

        // Set Timezone
        date_default_timezone_set('America/Mexico_City');

        // Paths
        $this->viewPath = __DIR__ . '/../Views';
        $this->baseUrl = '/momomy_bakery/';

        // Session Start
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // CSRF Protection
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        // Twig Setup
        $loader = new FilesystemLoader($this->viewPath);
        $this->twig = new Environment($loader, [
            'debug' => true,
        ]);

        // Global Variables for Views
        $this->twig->addGlobal('csrf_token', $_SESSION['csrf_token']);
        $this->twig->addGlobal('base_url', $this->baseUrl);
        $this->twig->addGlobal('session', $_SESSION);
        $this->twig->addGlobal('current_year', date('Y'));
        $this->twig->addGlobal('config', new \App\config\Config());
    }

    /**
     * Renders a Twig template and returns the HTML string
     */
    public function renderTemplate($view, $data = [])
    {
        return $this->twig->render($view, $data);
    }

    /**
     * Renders a Twig template and echoes it
     * @param string $view Template path relative to app/Views
     * @param array $data Data to pass to the template
     */
    public function showView($view, $data = [])
    {
        try {
            echo $this->renderTemplate($view, $data);
        } catch (\Exception $e) {
            echo "Error rendering view: " . $e->getMessage();
        }
    }

    /**
     * Method for JSON responses (API)
     */
    protected function jsonResponse($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect helper
     */
    protected function redirect($path)
    {
        header("Location: " . $this->baseUrl . ltrim($path, '/'));
        exit;
    }

    /**
     * Normalize Image URL to avoid double base_url and ensure correct path
     */
    protected function normalizeImageUrl($path)
    {
        if (empty($path)) return $this->baseUrl . 'public/uploads/products/placeholder.png';

        // If it already contains the full URL or starts with /momomy_bakery, return as is
        if (strpos($path, 'http') === 0 || strpos($path, $this->baseUrl) === 0) {
            return $path;
        }

        // If it starts with / but not base_url, it might be a root-relative path
        if (strpos($path, '/') === 0) {
            return $path;
        }

        // Otherwise, it's relative to the project root
        return $this->baseUrl . $path;
    }

    /**
     * Pagination Helper
     */
    protected function getPaginationData($totalItems, $itemsPerPage, $currentPage)
    {
        $totalPages = max(1, ceil($totalItems / $itemsPerPage));
        $currentPage = max(1, min($totalPages, (int)$currentPage));
        $offset = ($currentPage - 1) * $itemsPerPage;

        return [
            'current_page' => $currentPage,
            'total_pages'  => $totalPages,
            'offset'       => $offset,
            'limit'        => $itemsPerPage
        ];
    }
}
