<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->username) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Missing username or password"]);
    exit();
}

$username = $conn->real_escape_string($data->username);
$password = $conn->real_escape_string($data->password);

// Simple query - in production use prepared statements and password_hash/verify
$sql = "SELECT id, username, role FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["status" => "success", "user" => $row]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid credentials"]);
}

$conn->close();
?>
