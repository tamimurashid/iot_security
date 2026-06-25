<?php
// config/functions.php
session_start();

// Utility function to get a setting from database
function get_setting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

// Update a setting value
function update_setting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    $stmt->execute([$key, $value, $value]);
}

// Get all settings as associative array
function get_all_settings($pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// Get user by ID
function get_user($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT id, username, full_name, email, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Redirect if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: index.php");
        exit();
    }
}

// Generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed.');
    }
}

// Sanitize output
function h($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Format timestamp to "X ago" display
function format_time_ago($timestamp) {
    if (!$timestamp) return 'Never';
    $diff = time() - strtotime($timestamp);
    if ($diff < 5) return 'Just now';
    if ($diff < 60) return $diff . 's ago';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($timestamp));
}

// Simple encryption for storing API credentials
function encrypt_value($value) {
    if (empty($value)) return '';
    $key = 'iot_sec_k3y_2024!';
    $iv = substr(md5($key), 0, 16);
    return base64_encode(openssl_encrypt($value, 'AES-256-CBC', $key, 0, $iv));
}

function decrypt_value($value) {
    if (empty($value)) return '';
    $key = 'iot_sec_k3y_2024!';
    $iv = substr(md5($key), 0, 16);
    return openssl_decrypt(base64_decode($value), 'AES-256-CBC', $key, 0, $iv);
}

// Log System Action (backward compatible)
function log_system_action($pdo, $action, $user_id = null) {
    $stmt = $pdo->prepare("INSERT INTO system_logs (action, user_id) VALUES (?, ?)");
    $stmt->execute([$action, $user_id]);
}

// Log Audit Action (new, with IP and details)
function log_audit($pdo, $action, $details = null, $user_id = null) {
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $details, $ip]);
}

// API Key Validation
function validate_api_key($pdo, $key) {
    $stmt = $pdo->prepare("SELECT id FROM api_keys WHERE api_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() !== false;
}

// JSON response helper for API endpoints
function json_response($data, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json");
    echo json_encode($data);
    exit();
}

// Get count helper
function get_count($pdo, $query, $params = []) {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}
?>
