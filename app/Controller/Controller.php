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
    }

    /**
     * Renders a Twig template
     * @param string $view Template path relative to app/Views
     * @param array $data Data to pass to the template
     */
    public function showView($view, $data = [])
    {
        try {
            echo $this->twig->render($view, $data);
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
}
