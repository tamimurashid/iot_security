<?php
// api/device/config.php
require_once '../../config/database.php';
require_once '../../config/functions.php';

header("Content-Type: application/json");

$apiKey = $_GET['api_key'] ?? '';

if (!validate_api_key($pdo, $apiKey)) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

$settings = get_all_settings($pdo);

$config = [
    "status" => "success",
    "buzzer_mode_pir" => $settings['buzzer_mode_pir'] ?? 'beep',
    "buzzer_mode_laser" => $settings['buzzer_mode_laser'] ?? 'continuous',
    "buzzer_duration" => intval($settings['buzzer_duration'] ?? 2000),
    "pir_sensitivity" => $settings['pir_sensitivity'] ?? 'medium',
    "detection_cooldown" => intval($settings['detection_cooldown'] ?? 30),
    "motion_confirm_count" => intval($settings['motion_confirm_count'] ?? 1),
];

echo json_encode($config);
?>
