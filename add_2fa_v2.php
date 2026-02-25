<?php
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

$db = new Database();

try {
    // Add is_2fa_enabled to users table
    $db->query("ALTER TABLE users ADD COLUMN is_2fa_enabled TINYINT(1) DEFAULT 0 AFTER role_id");
    $db->execute();
    echo "Column is_2fa_enabled added to users table.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
