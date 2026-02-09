<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once '../db_connect.php';

session_destroy();
echo json_encode(["status" => "success", "message" => "Logged out successfully"]);
?>
