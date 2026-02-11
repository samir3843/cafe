<?php
$url = 'http://localhost/web-cafee/backend/api/auth.php';
$data = json_encode(['action' => 'login', 'email' => 'staff', 'password' => 'staff123']);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Curl Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: " . $httpCode . "\n";
    echo "Raw Response:\n" . $response . "\n";
}
curl_close($ch);
?>
