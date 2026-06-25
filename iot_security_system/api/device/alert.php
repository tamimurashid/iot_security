<?php
// api/device/alert.php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../includes/NotificationEngine.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->api_key) || !validate_api_key($pdo, $data->api_key)) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

$deviceId = $data->device_id ?? 'ESP32_NODE_01';
$alertType = $data->alert_type ?? 'Unknown Intrusion';
$sensorSource = $data->sensor ?? 'Unknown Sensor';
$confidence = $data->confidence ?? 100;

// Check if system is armed
$stmt = $pdo->prepare("SELECT security_mode, is_active FROM devices WHERE device_id = ?");
$stmt->execute([$deviceId]);
$device = $stmt->fetch();

$status = 'Unresolved';
if (!$device || $device['security_mode'] !== 'Armed' || !$device['is_active']) {
    $status = 'Ignored';
}

// Apply detection cooldown
$cooldown = (int)(get_setting($pdo, 'detection_cooldown') ?? 30);
if ($cooldown > 0 && $status === 'Unresolved') {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM alerts WHERE device_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL ? SECOND) AND status != 'Ignored'");
    $stmt->execute([$deviceId, $cooldown]);
    if ($stmt->fetchColumn() > 0) {
        $status = 'Cooldown';
    }
}

// Apply alert delay
$alertDelay = (int)(get_setting($pdo, 'alert_delay') ?? 0);

// Insert alert to database
$stmt = $pdo->prepare("INSERT INTO alerts (device_id, alert_type, sensor_source, confidence, status) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$deviceId, $alertType, $sensorSource, $confidence, $status]);
$alertId = $pdo->lastInsertId();

if ($status === 'Ignored') {
    json_response(["status" => "success", "message" => "Ignored - System is Disarmed or device inactive"]);
}

if ($status === 'Cooldown') {
    json_response(["status" => "success", "message" => "Cooldown active - alert recorded but notification skipped"]);
}

// Trigger Notifications (respects delay)
if ($alertDelay > 0) {
    sleep($alertDelay);
}

$notifier = new NotificationEngine($pdo);
$smsSent = $notifier->sendAlert($alertType, $sensorSource, $deviceId, $confidence);

// Update alert with SMS status
if ($smsSent) {
    $stmt = $pdo->prepare("UPDATE alerts SET sms_sent = 1 WHERE id = ?");
    $stmt->execute([$alertId]);
}

// Log device activity
$stmt = $pdo->prepare("INSERT INTO device_activity_logs (device_id, action, details) VALUES (?, ?, ?)");
$stmt->execute([$deviceId, 'Alert Triggered', "$alertType by $sensorSource (Confidence: $confidence%)"]);

json_response(["status" => "success", "message" => "Alert processed", "sms_sent" => $smsSent]);
?>
