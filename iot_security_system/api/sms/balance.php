<?php
// api/sms/balance.php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../includes/NotificationEngine.php';

header("Content-Type: application/json");

if (!is_logged_in()) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

$notifier = new NotificationEngine($pdo);
$result = $notifier->checkBalance();

if (isset($result['error'])) {
    json_response(["status" => "error", "message" => $result['error']], 500);
} else {
    json_response(["status" => "success", "balance" => $result['balance'], "data" => $result['raw'] ?? null]);
}
?>
