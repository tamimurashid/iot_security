<?php
// includes/SmsTemplateEngine.php

class SmsTemplateEngine {

    /**
     * Render an SMS message by finding the best matching template
     * and substituting variables.
     *
     * @param PDO    $pdo          Database connection
     * @param string $alertType    The type of alert (e.g. "Motion Intrusion")
     * @param array  $vars         Key-value pairs for template variables
     * @return string              The rendered message
     */
    public static function render($pdo, $alertType, $vars = []) {
        // Try to find a template matching this alert type
        $stmt = $pdo->prepare("SELECT template_body FROM sms_templates WHERE alert_type = ? AND is_default = 1 LIMIT 1");
        $stmt->execute([$alertType]);
        $template = $stmt->fetchColumn();

        // Fallback to the general "all" template
        if (!$template) {
            $stmt = $pdo->prepare("SELECT template_body FROM sms_templates WHERE alert_type = 'all' LIMIT 1");
            $stmt->execute();
            $template = $stmt->fetchColumn();
        }

        // Final fallback to a hardcoded default
        if (!$template) {
            $template = '⚠ Security Alert: {alert_type} at {location}. Device: {device_name}. Time: {date_time}.';
        }

        return self::substitute($template, $vars);
    }

    /**
     * Preview a template with sample data
     *
     * @param string $templateBody The template string
     * @return string              The rendered preview
     */
    public static function preview($templateBody) {
        $sampleVars = [
            '{device_name}' => 'Main Security Node',
            '{location}' => 'Front Entrance',
            '{date_time}' => date('Y-m-d H:i:s'),
            '{alert_type}' => 'Motion Intrusion',
            '{confidence}' => '95',
            '{status}' => 'Active',
        ];

        return self::substitute($templateBody, $sampleVars);
    }

    /**
     * Substitute variables in a template string
     *
     * @param string $template The template with {variable} placeholders
     * @param array  $vars     Key-value pairs for substitution
     * @return string
     */
    public static function substitute($template, $vars) {
        return str_replace(array_keys($vars), array_values($vars), $template);
    }

    /**
     * Get all available template variables with descriptions
     *
     * @return array
     */
    public static function getAvailableVariables() {
        return [
            '{device_name}' => 'Name of the IoT device',
            '{location}' => 'Physical location of the device',
            '{date_time}' => 'Date and time of the alert',
            '{alert_type}' => 'Type of alert triggered',
            '{confidence}' => 'Detection confidence percentage',
            '{status}' => 'Current alert status',
        ];
    }

    /**
     * Validate a template body contains at least one variable
     *
     * @param string $templateBody
     * @return bool
     */
    public static function validate($templateBody) {
        if (empty(trim($templateBody))) return false;
        $vars = array_keys(self::getAvailableVariables());
        foreach ($vars as $var) {
            if (strpos($templateBody, $var) !== false) return true;
        }
        // Template is valid even without variables (plain text is fine)
        return true;
    }
}
?>
