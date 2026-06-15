<?php
// config/functions.php
session_start();

// Utility function to get settings from database
function get_setting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
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
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Log System Action
function log_system_action($pdo, $action, $user_id = null) {
    $stmt = $pdo->prepare("INSERT INTO system_logs (action, user_id) VALUES (?, ?)");
    $stmt->execute([$action, $user_id]);
}

// API Key Validation
function validate_api_key($pdo, $key) {
    $stmt = $pdo->prepare("SELECT id FROM api_keys WHERE api_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetchColumn() !== false;
}
?>
