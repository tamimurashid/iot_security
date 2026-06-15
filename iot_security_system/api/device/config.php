<?php
// api/device/config.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header("Content-Type: application/json");

$apiKey = $_GET['api_key'] ?? '';

if (!validate_api_key($pdo, $apiKey)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// In a real scenario, you might pull these from the `settings` table
$config = [
    "status" => "success",
    "ldr_threshold" => 500, // Example threshold for laser break detection
    "heartbeat_interval" => 30000 // 30 seconds
];

echo json_encode($config);
?>
