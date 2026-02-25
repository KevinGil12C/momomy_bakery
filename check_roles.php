<?php
require_once __DIR__ . '/app/Models/Database.php';
$db = new App\Models\Database();
print_r($db->rawQuery("SELECT * FROM roles"));
print_r($db->rawQuery("SELECT * FROM users LIMIT 1"));
