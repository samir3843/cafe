<?php
// Mock $_POST input via php://input
// This is tricky for php-cgi, but we can just include the file and mock the input if we modify auth.php slightly or just rely on manual testing with a different script.

// Instead, let's just make a script that does what auth.php does but prints everything.

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'backend/db_connect.php';

$data = ['action' => 'login', 'email' => 'staff', 'password' => 'staff123'];
// Manually checking logic
$email = $data['email'];
$password = $data['password'];

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR username = ?");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    
    if ($user) {
        echo "User found: " . print_r($user, true);
    } else {
        echo "User not found";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
