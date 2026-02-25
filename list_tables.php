<?php
require_once __DIR__ . '/app/Models/Database.php';
$db = new App\Models\Database();
$tables = $db->rawQuery("SHOW TABLES");
print_r($tables);
