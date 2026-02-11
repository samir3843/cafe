<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->password) || !isset($data->fullname) || !isset($data->email)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

$username = $conn->real_escape_string($data->username);
$password = $conn->real_escape_string($data->password); // In production, hash this!
$fullname = $conn->real_escape_string($data->fullname);
$email = $conn->real_escape_string($data->email);

// Default role is receptionist for new signups
$role = 'receptionist'; 

$check_sql = "SELECT id FROM users WHERE username = '$username'";
if ($conn->query($check_sql)->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Username already exists"]);
    exit();
}

$sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";

if ($conn->query($sql) === TRUE) {
    $user_id = $conn->insert_id;
    // Auto-login after signup
    $_SESSION['user_id'] = $user_id;
    $_SESSION['role'] = $role;
    $_SESSION['username'] = $username;
    
    echo json_encode(["status" => "success", "message" => "User registered successfully", "user" => ["id" => $user_id, "username" => $username, "role" => $role]]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}

$conn->close();
?>
