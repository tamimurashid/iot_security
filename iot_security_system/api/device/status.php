<?php
// api/device/status.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header("Content-Type: application/json");

$apiKey = $_GET['api_key'] ?? '';

if (!validate_api_key($pdo, $apiKey)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$deviceId = $_GET['device_id'] ?? 'ESP32_NODE_01';
$stmt = $pdo->prepare("SELECT security_mode FROM devices WHERE device_id = ?");
$stmt->execute([$deviceId]);
$device = $stmt->fetch();

if ($device) {
    echo json_encode(["status" => "success", "mode" => $device['security_mode']]);
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Device not found"]);
}
?>
