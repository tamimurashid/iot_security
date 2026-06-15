<?php
// api/device/update.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header("Content-Type: application/json");

// Read POST data
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->api_key) || !validate_api_key($pdo, $data->api_key)) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$deviceId = $data->device_id ?? 'ESP32_NODE_01';
$pirStatus = $data->pir ?? 0;
$laserStatus = $data->laser ?? 0;
$ldrValue = $data->ldr ?? 0;

$stmt = $pdo->prepare("UPDATE devices SET 
    status = 'Online',
    pir_status = ?,
    laser_status = ?,
    ldr_value = ?,
    last_communication = CURRENT_TIMESTAMP
    WHERE device_id = ?");
    
$result = $stmt->execute([$pirStatus, $laserStatus, $ldrValue, $deviceId]);

if ($result) {
    echo json_encode(["status" => "success", "message" => "Data updated"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to update"]);
}
?>
