<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/Models/Database.php';

use App\Models\Database;

$db = new Database();
$db->query("DESCRIBE contacts");
print_r($db->resultSet());
