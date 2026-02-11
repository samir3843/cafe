<?php
require_once 'db_connect.php';

try {
    $sql = file_get_contents(__DIR__ . '/schema_update.sql');
    $pdo->exec($sql);
    echo "Database schema updated successfully.\n";
} catch (PDOException $e) {
    die("DB ERROR: " . $e->getMessage());
}
?>
