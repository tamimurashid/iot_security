<?php
// settings.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    // SMS Settings
    $settingsToUpdate = [
        'sms_api_url' => $_POST['sms_api_url'] ?? '',
        'sms_api_token' => $_POST['sms_api_token'] ?? '',
        'sms_sender_name' => $_POST['sms_sender_name'] ?? '',
        'sms_recipient' => $_POST['sms_recipient'] ?? '',
        'sms_enabled' => isset($_POST['sms_enabled']) ? '1' : '0',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        'email_sender' => $_POST['email_sender'] ?? '',
        'email_sender_name' => $_POST['email_sender_name'] ?? '',
        'email_recipient' => $_POST['email_recipient'] ?? '',
        'email_enabled' => isset($_POST['email_enabled']) ? '1' : '0'
    ];
    
    foreach ($settingsToUpdate as $key => $value) {
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->execute([$value, $key]);
    }
    
    log_system_action($pdo, "Settings Updated", $_SESSION['user_id']);
    $successMessage = "Settings saved successfully.";
}

// Load current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$currentSettings = [];
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - IoT Security System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-shield"></i> IoT Sec</h2>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="alerts.php"><i class="fa-solid fa-bell"></i> Alerts</a></li>
                <li class="active"><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="topbar">
                <h1>Settings</h1>
            </header>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success"><?= h($successMessage) ?></div>
            <?php endif; ?>

            <form method="POST" action="settings.php">
                <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                
                <div class="dashboard-grid">
                    <div class="panel">
                        <h2><i class="fa-solid fa-comment-sms"></i> SMS Settings (Beam Africa)</h2>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="sms_enabled" name="sms_enabled" value="1" <?= ($currentSettings['sms_enabled'] == '1') ? 'checked' : '' ?>>
                            <label for="sms_enabled">Enable SMS Notifications</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="sms_api_url">API URL</label>
                            <input type="url" id="sms_api_url" name="sms_api_url" value="<?= h($currentSettings['sms_api_url'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="sms_api_token">API Token</label>
                            <input type="text" id="sms_api_token" name="sms_api_token" value="<?= h($currentSettings['sms_api_token'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="sms_sender_name">Sender Name</label>
                            <input type="text" id="sms_sender_name" name="sms_sender_name" value="<?= h($currentSettings['sms_sender_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="sms_recipient">Recipient Phone (Comma separated)</label>
                            <input type="text" id="sms_recipient" name="sms_recipient" value="<?= h($currentSettings['sms_recipient'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="panel">
                        <h2><i class="fa-solid fa-envelope"></i> Email Settings (SMTP)</h2>
                        
                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="email_enabled" name="email_enabled" value="1" <?= ($currentSettings['email_enabled'] == '1') ? 'checked' : '' ?>>
                            <label for="email_enabled">Enable Email Notifications</label>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" value="<?= h($currentSettings['smtp_host'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group" style="display:flex; gap:10px;">
                            <div style="flex:1;">
                                <label for="smtp_port">SMTP Port</label>
                                <input type="number" id="smtp_port" name="smtp_port" value="<?= h($currentSettings['smtp_port'] ?? '') ?>">
                            </div>
                            <div style="flex:1;">
                                <label for="smtp_username">SMTP Username</label>
                                <input type="text" id="smtp_username" name="smtp_username" value="<?= h($currentSettings['smtp_username'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" value="<?= h($currentSettings['smtp_password'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email_sender">Sender Email</label>
                            <input type="email" id="email_sender" name="email_sender" value="<?= h($currentSettings['email_sender'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email_sender_name">Sender Name</label>
                            <input type="text" id="email_sender_name" name="email_sender_name" value="<?= h($currentSettings['email_sender_name'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email_recipient">Recipient Email(s)</label>
                            <input type="text" id="email_recipient" name="email_recipient" value="<?= h($currentSettings['email_recipient'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                
                <div class="panel mt-20" style="text-align: right;">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
