<?php
include 'c:/xampp/htdocs/web-cafee/backend/db_connect.php';
$res = $conn->query("SELECT count(*) as c FROM users");
if ($res) {
    $row = $res->fetch_assoc();
    echo "Users: " . $row['c'] . "\n";
} else {
    echo "Error: " . $conn->error . "\n";
}
?>
