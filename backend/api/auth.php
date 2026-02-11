<?php
session_start();
// Disable HTML errors to prevent breaking output, though we are redirecting mostly
ini_set('display_errors', 0);
require_once '../db_connect.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Handle Form Submission
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            redirectError("Email and password required");
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR username = ?");
            $stmt->execute([$email, $email]);
            $user = $stmt->fetch();

            if ($user && $user['password'] === $password) {
                // Login Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: ../../frontend/dashboard-admin.php');
                        break;
                    case 'staff':
                        header('Location: ../../frontend/dashboard-staff.php');
                        break;
                    case 'kitchen':
                        header('Location: ../../frontend/dashboard-kitchen.php');
                        break;
                    default:
                        header('Location: ../../frontend/index.html');
                }
                exit;
            } else {
                redirectError("Invalid credentials");
            }
        } catch (Exception $e) {
            redirectError("System Error");
        }
    } elseif ($action === 'register') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $fullname = $_POST['fullname'] ?? '';
        $role = 'staff'; // Default

        if (empty($username) || empty($password)) {
            redirectError("Username and password required", "register");
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                redirectError("Username already exists", "register");
            }

            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $password, $role])) {
                // Registration success, redirect to login with success message
                header('Location: ../../frontend/login-form.html?success=registered');
                exit;
            } else {
                redirectError("Registration failed", "register");
            }
        } catch (Exception $e) {
            redirectError("System Error", "register");
        }
    }
} elseif ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ../../frontend/login-form.html');
    exit;
}

function redirectError($msg, $mode = 'login') {
    $msg = urlencode($msg);
    if ($mode === 'login') {
        header("Location: ../../frontend/login-form.html?error=$msg");
    } else {
        header("Location: ../../frontend/login-form.html?error=$msg&mode=signup");
    }
    exit;
}
