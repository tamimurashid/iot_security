<?php
// includes/NotificationEngine.php

// PHPMailer (kept for email functionality)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/SmsTemplateEngine.php';

class NotificationEngine {
    private $pdo;
    private const BEEM_SMS_URL = 'https://apisms.beem.africa/v1/send';
    private const BEEM_BALANCE_URL = 'https://apitopup.beem.africa/v1/credit-balance';

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Send alert notification via SMS and/or Email
     */
    public function sendAlert($alertType, $sensorSource, $deviceId = 'ESP32_NODE_01', $confidence = 100) {
        $smsEnabled = get_setting($this->pdo, 'sms_enabled');
        $emailEnabled = get_setting($this->pdo, 'email_enabled');

        // Check alert trigger mode
        $triggerMode = get_setting($this->pdo, 'alert_trigger_mode') ?? 'both';
        if (!$this->shouldSendAlert($triggerMode, $sensorSource, $confidence)) {
            return false;
        }

        // Check confidence threshold
        $threshold = (int)(get_setting($this->pdo, 'confidence_threshold') ?? 50);
        if ($confidence < $threshold) {
            return false;
        }

        // Get device details for template
        $device = $this->getDevice($deviceId);
        $templateVars = [
            '{device_name}' => $device['device_name'] ?? $deviceId,
            '{location}' => $device['location'] ?? 'Unknown',
            '{date_time}' => date('Y-m-d H:i:s'),
            '{alert_type}' => $alertType,
            '{confidence}' => $confidence,
            '{status}' => 'Active',
        ];

        $smsSent = false;
        if ($smsEnabled == '1') {
            $message = SmsTemplateEngine::render($this->pdo, $alertType, $templateVars);
            $smsSent = $this->sendSMS($message);
        }

        if ($emailEnabled == '1') {
            $this->sendEmail($alertType, $sensorSource);
        }

        return $smsSent;
    }

    /**
     * Determine if alert should be sent based on trigger mode
     */
    private function shouldSendAlert($mode, $sensorSource, $confidence) {
        switch ($mode) {
            case 'pir_only':
                return strtolower($sensorSource) === 'pir';
            case 'camera_only':
                return strtolower($sensorSource) === 'camera';
            case 'both':
                return true;
            case 'high_confidence':
                return $confidence >= 80;
            case 'critical':
                return $confidence >= 95;
            default:
                return true;
        }
    }

    /**
     * Get device details
     */
    private function getDevice($deviceId) {
        $stmt = $this->pdo->prepare("SELECT * FROM devices WHERE device_id = ?");
        $stmt->execute([$deviceId]);
        return $stmt->fetch() ?: [];
    }

    /**
     * Send SMS via Beem Africa API
     * Uses Basic Auth (api_key:secret_key) and JSON body
     */
    private function sendSMS($message, $recipientOverride = null) {
        $apiKey = get_setting($this->pdo, 'sms_api_key');
        $secretKey = get_setting($this->pdo, 'sms_secret_key');
        $senderName = get_setting($this->pdo, 'sms_sender_name') ?: 'INFO';
        $recipient = $recipientOverride ?: get_setting($this->pdo, 'sms_recipient');

        if (empty($apiKey) || empty($secretKey) || empty($recipient)) {
            $this->logSMS($recipient ?? '', $message, 'Failed - Missing config');
            return false;
        }

        // Build recipients array (supports comma-separated numbers)
        $numbers = array_map('trim', explode(',', $recipient));
        $recipients = [];
        foreach ($numbers as $i => $number) {
            $recipients[] = [
                'recipient_id' => $i + 1,
                'dest_addr' => $number
            ];
        }

        // Strip non-ASCII characters (like emojis) to prevent HTTP 400 errors with encoding 0
        $cleanMessage = preg_replace('/[^\x20-\x7E\n\r]/', '', $message);

        $payload = [
            'source_addr' => $senderName,
            'schedule_time' => '',
            'encoding' => 0,
            'message' => trim($cleanMessage),
            'recipients' => $recipients
        ];

        $ch = curl_init(self::BEEM_SMS_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:$secretKey");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $status = ($httpCode == 200) ? 'Success' : 'Failed - HTTP ' . $httpCode;
        if ($curlError) {
            $status = 'Failed - ' . $curlError;
        }

        $this->logSMS($recipient, $message, $status, $response);

        return $httpCode == 200;
    }

    /**
     * Send a test SMS
     */
    public function sendTestSMS($recipient, $message) {
        return $this->sendSMS($message, $recipient);
    }

    /**
     * Check Beem Africa SMS credit balance
     */
    public function checkBalance() {
        $apiKey = get_setting($this->pdo, 'sms_api_key');
        $secretKey = get_setting($this->pdo, 'sms_secret_key');

        if (empty($apiKey) || empty($secretKey)) {
            return ['error' => 'API credentials not configured'];
        }

        $url = self::BEEM_BALANCE_URL . '?app_name=IoTSecurity';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$apiKey:$secretKey");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 200) {
            $data = json_decode($response, true);
            return ['balance' => $data['data']['credit_balance'] ?? 'N/A', 'raw' => $data];
        }

        return ['error' => 'Failed to fetch balance (HTTP ' . $httpCode . ')', 'response' => $response];
    }

    /**
     * Send email via SMTP (preserved from original)
     */
    private function sendEmail($alertType, $sensorSource) {
        $smtpHost = get_setting($this->pdo, 'smtp_host');
        $smtpPort = get_setting($this->pdo, 'smtp_port');
        $smtpUser = get_setting($this->pdo, 'smtp_username');
        $smtpPass = get_setting($this->pdo, 'smtp_password');
        $senderEmail = get_setting($this->pdo, 'email_sender');
        $senderName = get_setting($this->pdo, 'email_sender_name');
        $recipient = get_setting($this->pdo, 'email_recipient');

        if (empty($smtpUser) || empty($recipient)) {
            $this->logEmail($recipient, "Intrusion Alert", 'Failed - Missing config');
            return false;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($recipient);

            $mail->isHTML(true);
            $mail->Subject = 'IoT Security System - Intrusion Alert';

            $htmlContent = "
                <h2>Security Alert</h2>
                <p>An intrusion has been detected by your IoT Security System.</p>
                <ul>
                    <li><strong>Alert Type:</strong> {$alertType}</li>
                    <li><strong>Sensor Source:</strong> {$sensorSource}</li>
                    <li><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</li>
                    <li><strong>System:</strong> {$senderName}</li>
                </ul>
                <p>Please log in to your dashboard to view more details.</p>
            ";

            $mail->Body    = $htmlContent;
            $mail->AltBody = strip_tags($htmlContent);

            $mail->send();
            $this->logEmail($recipient, $mail->Subject, 'Success');
            return true;
        } catch (Exception $e) {
            $this->logEmail($recipient, 'Intrusion Alert', 'Failed: ' . $mail->ErrorInfo);
            return false;
        }
    }

    private function logSMS($recipient, $message, $status, $response = null) {
        $stmt = $this->pdo->prepare("INSERT INTO sms_logs (recipient, message, status) VALUES (?, ?, ?)");
        $stmt->execute([$recipient, $message, $status]);
    }

    private function logEmail($recipient, $subject, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO email_logs (recipient, subject, status) VALUES (?, ?, ?)");
        $stmt->execute([$recipient, $subject, $status]);
    }
}
?>
