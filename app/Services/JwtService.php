<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Service to handle JWT using firebase/php-jwt library
 */
class JwtService
{
    private $secretKey;
    private $algorithm = 'HS256';

    public function __construct()
    {
        $this->secretKey = "MOMOMY_BAKERY_SECRET_KEY_123!"; // Should be in a config/env file
    }

    /**
     * Generate a new JWT token
     */
    public function generateToken($data, $expiry = 3600)
    {
        $payload = array_merge($data, [
            'iat' => time(),
            'exp' => time() + $expiry
        ]);

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Validate and decode a JWT token
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (\Exception $e) {
            error_log("JWT Validation failed: " . $e->getMessage());
            return false;
        }
    }
}
