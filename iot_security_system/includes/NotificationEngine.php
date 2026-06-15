<?php
// includes/NotificationEngine.php

// Note: For a production environment, you should use Composer to install PHPMailer.
// This assumes PHPMailer files are manually placed in includes/PHPMailer/
// Require PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class NotificationEngine {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function sendAlert($alertType, $sensorSource) {
        $smsEnabled = get_setting($this->pdo, 'sms_enabled');
        $emailEnabled = get_setting($this->pdo, 'email_enabled');
        
        $message = "ALERT: " . $alertType . " detected by " . $sensorSource . " at " . date('Y-m-d H:i:s');
        
        if ($smsEnabled == '1') {
            $this->sendSMS($message);
        }
        
        if ($emailEnabled == '1') {
            $this->sendEmail($alertType, $sensorSource);
        }
    }

    private function sendSMS($message) {
        $apiUrl = get_setting($this->pdo, 'sms_api_url');
        $apiToken = get_setting($this->pdo, 'sms_api_token');
        $senderName = get_setting($this->pdo, 'sms_sender_name');
        $recipient = get_setting($this->pdo, 'sms_recipient');
        
        if (empty($apiToken) || empty($recipient)) {
            $this->logSMS($recipient, $message, 'Failed - Missing config');
            return false;
        }

        // Beam Africa API Payload format (Adjust according to actual documentation)
        $payload = [
            'token' => $apiToken,
            'sender' => $senderName,
            'to' => $recipient,
            'message' => $message
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $status = ($httpCode == 200) ? 'Success' : 'Failed - HTTP ' . $httpCode;
        $this->logSMS($recipient, $message, $status);
        
        return $httpCode == 200;
    }

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
            //Server settings
            $mail->isSMTP();
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $smtpPort;

            //Recipients
            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($recipient);

            //Content
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

    private function logSMS($recipient, $message, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO sms_logs (recipient, message, status) VALUES (?, ?, ?)");
        $stmt->execute([$recipient, $message, $status]);
    }

    private function logEmail($recipient, $subject, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO email_logs (recipient, subject, status) VALUES (?, ?, ?)");
        $stmt->execute([$recipient, $subject, $status]);
    }
}
?>
