<?php

namespace App\Controller;

use App\Models\Database;
use App\Services\TwoFactorService;

/**
 * AuthController handles Login, Logout, and 2FA for the Admin System
 */
class AuthController extends Controller
{
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = new Database();
    }

    /**
     * Show Login Form
     */
    public function showLogin()
    {
        if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
            $this->redirect('admin/dashboard');
        }
        $this->showView('admin/auth/login.twig', [
            'title' => 'Admin Login'
        ]);
    }

    /**
     * Handle Login Submission
     */
    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $results = $this->db->rawQuery("SELECT users.*, roles.name as role 
                                        FROM users 
                                        JOIN roles ON users.role_id = roles.id 
                                        WHERE users.email = :email LIMIT 1", [':email' => $email]);
        $user = $results[0] ?? null;

        if ($user && password_verify($password, $user['password'])) {
            // Check if 2FA is needed
            if ($user['role'] === 'admin' && $user['is_2fa_enabled'] == 1) {
                $_SESSION['pending_2fa_user'] = $user['id'];

                // Generate and send 2FA code
                $tfa = new TwoFactorService();
                $code = $tfa->generateCode();
                $tfa->storeCode($user['id'], $code);

                // SEND REAL EMAIL
                $emailService = new \App\Services\EmailService();
                $emailService->send(
                    $user['email'],
                    "Código de Seguridad - Momomy Bakery",
                    "Hola {$user['first_name']}, tu código de acceso al sistema es: <b>$code</b>. Este código expira en 10 minutos."
                );

                $this->redirect('admin/2fa');
            } elseif ($user['role'] === 'admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_avatar'] = $user['avatar_url'];
                $this->redirect('admin/dashboard');
            } else {
                $this->showView('admin/auth/login.twig', [
                    'error' => 'Acceso denegado. Esta cuenta no tiene permisos de administrador.',
                    'email' => $email
                ]);
            }
        } else {
            $this->showView('admin/auth/login.twig', [
                'error' => 'Credenciales inválidas',
                'email' => $email
            ]);
        }
    }

    /**
     * Show 2FA Form
     */
    public function show2FA()
    {
        if (!isset($_SESSION['pending_2fa_user'])) {
            $this->redirect('login');
        }
        $this->showView('admin/auth/2fa.twig', [
            'title' => 'Verificación de 2 Factores'
        ]);
    }

    /**
     * Verify 2FA Code
     */
    public function verify2FA()
    {
        $code = $_POST['code'] ?? '';
        $tfa = new TwoFactorService();

        // UNIVERSAL DEV CODE: 111111
        if ($tfa->validateCode($code) || $code === '111111') {
            $userId = $_SESSION['pending_2fa_user'];
            $results = $this->db->rawQuery("SELECT users.*, roles.name as role 
                                            FROM users 
                                            JOIN roles ON users.role_id = roles.id 
                                            WHERE users.id = :id LIMIT 1", [':id' => $userId]);
            $user = $results[0] ?? null;

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_avatar'] = $user['avatar_url'];
            unset($_SESSION['pending_2fa_user']);

            $this->redirect('admin/dashboard');
        } else {
            $this->showView('admin/auth/2fa.twig', [
                'error' => 'Código incorrecto o expirado. (Dev Tip: Prueba con 111111)'
            ]);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        $this->redirect('login');
    }
}
