<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once '../db_connect.php';

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$date = $conn->real_escape_string($date);

// Query orders for the specific date, joined with user info
$sql = "SELECT o.id, o.customer_name, o.table_number, o.order_details, o.order_date, u.username as receptionist_name 
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE DATE(o.order_date) = '$date'
        ORDER BY o.customer_name";

$result = $conn->query($sql);

$orders = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

echo json_encode(["status" => "success", "date" => $date, "orders" => $orders]);

$conn->close();
?>
