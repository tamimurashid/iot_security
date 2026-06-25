<?php
// api/device/register.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->api_key) || !validate_api_key($pdo, $data->api_key)) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

$deviceId = $data->device_id ?? '';
$deviceName = $data->device_name ?? $deviceId;
$location = $data->location ?? '';
$firmware = $data->firmware_version ?? 'Unknown';

if (empty($deviceId)) {
    json_response(["status" => "error", "message" => "device_id is required"], 400);
}

// Check if device already exists
$stmt = $pdo->prepare("SELECT id FROM devices WHERE device_id = ?");
$stmt->execute([$deviceId]);
if ($stmt->fetch()) {
    json_response(["status" => "error", "message" => "Device already registered"], 409);
}

$stmt = $pdo->prepare("INSERT INTO devices (device_id, device_name, location, firmware_version, status) VALUES (?, ?, ?, ?, 'Offline')");
$stmt->execute([$deviceId, $deviceName, $location, $firmware]);

$stmt = $pdo->prepare("INSERT INTO device_activity_logs (device_id, action, details) VALUES (?, ?, ?)");
$stmt->execute([$deviceId, 'Registered', "Device registered via API"]);

json_response(["status" => "success", "message" => "Device registered successfully"], 201);
?>
