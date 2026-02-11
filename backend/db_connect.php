<?php
// Disable HTML errors to prevent breaking JSON
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('html_errors', 0);
error_reporting(E_ALL);

// Log errors to a file instead
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

$host = 'localhost';
$dbname = 'Cafe';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Return JSON error and exit
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => "DB Connection Failed: " . $e->getMessage()]);
    exit;
}
// checking trailing whitespace issues by not closing php tag if file is pure php (recommended practice)

