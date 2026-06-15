<?php
// api/device/alert.php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../includes/NotificationEngine.php';

header("Content-Type: application/json");

// Read POST data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->api_key) || !validate_api_key($pdo, $data->api_key)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Check if system is armed before processing alert
$deviceId = $data->device_id ?? 'ESP32_NODE_01';
$stmt = $pdo->prepare("SELECT security_mode FROM devices WHERE device_id = ?");
$stmt->execute([$deviceId]);
$device = $stmt->fetch();

if (!$device || $device['security_mode'] !== 'Armed') {
    echo json_encode(["status" => "success", "message" => "Ignored - System is Disarmed"]);
    exit();
}

$alertType = $data->alert_type ?? 'Unknown Intrusion';
$sensorSource = $data->sensor ?? 'Unknown Sensor';

// Insert alert to database
$stmt = $pdo->prepare("INSERT INTO alerts (alert_type, sensor_source) VALUES (?, ?)");
$stmt->execute([$alertType, $sensorSource]);

// Trigger Notifications
$notifier = new NotificationEngine($pdo);
$notifier->sendAlert($alertType, $sensorSource);

echo json_encode(["status" => "success", "message" => "Alert processed"]);
?>
