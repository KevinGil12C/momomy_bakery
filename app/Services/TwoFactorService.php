<?php

namespace App\Services;

/**
 * Service to handle 2FA via email or Google Authenticator logic
 */
class TwoFactorService
{
    /**
     * Generates a random 6-digit code
     */
    public function generateCode()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Stores the code in session for validation
     */
    public function storeCode($userId, $code)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['2fa_code'] = [
            'user_id' => $userId,
            'code' => $code,
            'expires' => time() + 600 // 10 minutes
        ];
    }

    /**
     * Validates the provided code
     */
    public function validateCode($code)
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['2fa_code'])) return false;

        $stored = $_SESSION['2fa_code'];
        if (time() > $stored['expires']) {
            unset($_SESSION['2fa_code']);
            return false;
        }

        if ($code == $stored['code']) {
            unset($_SESSION['2fa_code']);
            return true;
        }

        return false;
    }
}
