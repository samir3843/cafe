<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->customer_name) || !isset($data->table_number) || !isset($data->order_details) || !isset($data->user_id)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

$customer_name = $conn->real_escape_string($data->customer_name);
$table_number = (int)$data->table_number;
$order_details = $conn->real_escape_string($data->order_details);
$user_id = (int)$data->user_id;

$sql = "INSERT INTO orders (customer_name, table_number, order_details, user_id) VALUES ('$customer_name', $table_number, '$order_details', $user_id)";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Order created successfully", "order_id" => $conn->insert_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}

$conn->close();
?>
