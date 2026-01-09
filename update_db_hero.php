<?php
require_once 'includes/db_connect.php';

try {
    // Add is_hero column if it doesn't exist
    $pdo->exec("ALTER TABLE news ADD COLUMN is_hero TINYINT(1) DEFAULT 0");
    echo "Added 'is_hero' column successfully.<br>";
} catch (PDOException $e) {
    echo "Column 'is_hero' likely already exists or other error: " . $e->getMessage() . "<br>";
}

echo "Database update complete.";
?>
