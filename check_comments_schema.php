<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=momomy_bakery', 'root', '');
    $table = 'product_comments';
    echo "Columns in $table:\n";
    $stmt = $db->query("DESCRIBE $table");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo " - {$row['Field']} ({$row['Type']})\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
