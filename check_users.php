<?php
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

$db = new Database();

try {
    $cols = $db->rawQuery("SHOW COLUMNS FROM users");
    print_r($cols);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
