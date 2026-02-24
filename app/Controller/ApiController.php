<?php

namespace App\Controller;

use App\Services\EmailService;
use App\Services\JwtService;

class ApiController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        // For API, we might want to disable CSRF check or use Token Auth
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

        // Use Email Service
        $email = new EmailService();
        $success = $email->send(
            'admin@momomy.com',
            'Nuevo Contacto desde Vercel',
            "Nombre: {$data['name']}<br>Email: {$data['email']}<br>Mensaje: {$data['message']}"
        );

        if ($success) {
            return $this->jsonResponse(['message' => 'Email sent successfully']);
        } else {
            return $this->jsonResponse(['error' => 'Email failed'], 500);
        }
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
}
