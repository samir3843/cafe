<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include_once '../db_connect.php';

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => "authenticated", 
        "user" => [
            "id" => $_SESSION['user_id'], 
            "username" => $_SESSION['username'], 
            "role" => $_SESSION['role']
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(["status" => "unauthenticated"]);
}
?>
