<?php
// api/sms/test.php
require_once '../../config/database.php';
require_once '../../config/functions.php';
require_once '../../includes/NotificationEngine.php';

header("Content-Type: application/json");

if (!is_logged_in()) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

$data = json_decode(file_get_contents("php://input"));
$recipient = $data->recipient ?? '';
$message = $data->message ?? 'Test SMS from IoT Security System';

if (empty($recipient)) {
    json_response(["status" => "error", "message" => "Recipient phone number is required"], 400);
}

$notifier = new NotificationEngine($pdo);
$result = $notifier->sendTestSMS($recipient, $message);

log_audit($pdo, 'Test SMS Sent', "Recipient: $recipient");

if ($result) {
    json_response(["status" => "success", "message" => "Test SMS sent successfully"]);
} else {
    json_response(["status" => "error", "message" => "Failed to send test SMS. Check your Beem Africa credentials."], 500);
}
?>
