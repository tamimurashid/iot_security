<?php
// api/device/update.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->api_key) || !validate_api_key($pdo, $data->api_key)) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

$deviceId = $data->device_id ?? 'ESP32_NODE_01';
$pirStatus = $data->pir ?? 0;
$laserStatus = $data->laser ?? 0;
$ldrValue = $data->ldr ?? 0;
$firmwareVersion = $data->firmware ?? null;

// Check if device exists, auto-register if not
$stmt = $pdo->prepare("SELECT id, status FROM devices WHERE device_id = ?");
$stmt->execute([$deviceId]);
$existingDevice = $stmt->fetch();

if (!$existingDevice) {
    // Auto-register new device
    $stmt = $pdo->prepare("INSERT INTO devices (device_id, device_name, status, pir_status, laser_status, ldr_value) VALUES (?, ?, 'Online', ?, ?, ?)");
    $stmt->execute([$deviceId, $deviceId, $pirStatus, $laserStatus, $ldrValue]);

    // Log activity
    $stmt = $pdo->prepare("INSERT INTO device_activity_logs (device_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$deviceId, 'Auto-Registered', 'Device registered automatically via heartbeat']);

    json_response(["status" => "success", "message" => "Device registered and data updated"]);
}

// Track status change
$wasOffline = ($existingDevice['status'] === 'Offline');

// Update device data
$updateFields = "status = 'Online', pir_status = ?, laser_status = ?, ldr_value = ?, last_communication = CURRENT_TIMESTAMP";
$params = [$pirStatus, $laserStatus, $ldrValue];

if ($firmwareVersion) {
    $updateFields .= ", firmware_version = ?";
    $params[] = $firmwareVersion;
}

$params[] = $deviceId;
$stmt = $pdo->prepare("UPDATE devices SET $updateFields WHERE device_id = ?");
$result = $stmt->execute($params);

// Log reconnection
if ($wasOffline) {
    $stmt = $pdo->prepare("INSERT INTO device_activity_logs (device_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([$deviceId, 'Came Online', 'Device reconnected']);
}

if ($result) {
    json_response(["status" => "success", "message" => "Data updated"]);
} else {
    json_response(["status" => "error", "message" => "Failed to update"], 500);
}
?>
