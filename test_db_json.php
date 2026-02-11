<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'backend/db_connect.php';

echo json_encode(['status' => 'ok', 'message' => 'Connection successful']);
?>
