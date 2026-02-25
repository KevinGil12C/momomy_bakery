<?php
require_once __DIR__ . '/app/config/database.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

$tables = ['users', 'products', 'orders', 'quotations'];
foreach ($tables as $table) {
    echo "--- Table: $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    while ($row = $res->fetch_assoc()) {
        printf("%-20s %-20s %-10s\n", $row['Field'], $row['Type'], $row['Null']);
    }
    echo "\n";
}
