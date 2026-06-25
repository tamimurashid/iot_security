<?php
// settings.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

$pageTitle = 'Settings';
$currentPage = 'settings';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $settingsToUpdate = [
        // Beem Africa SMS
        'sms_api_key' => $_POST['sms_api_key'] ?? '',
        'sms_secret_key' => $_POST['sms_secret_key'] ?? '',
        'sms_sender_name' => $_POST['sms_sender_name'] ?? 'INFO',
        'sms_recipient' => $_POST['sms_recipient'] ?? '',
        'sms_enabled' => isset($_POST['sms_enabled']) ? '1' : '0',
        // SMTP Email
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_username' => $_POST['smtp_username'] ?? '',
        'smtp_password' => $_POST['smtp_password'] ?? '',
        'email_sender' => $_POST['email_sender'] ?? '',
        'email_sender_name' => $_POST['email_sender_name'] ?? '',
        'email_recipient' => $_POST['email_recipient'] ?? '',
        'email_enabled' => isset($_POST['email_enabled']) ? '1' : '0',
        // Buzzer
        'buzzer_mode_pir' => $_POST['buzzer_mode_pir'] ?? 'beep',
        'buzzer_mode_laser' => $_POST['buzzer_mode_laser'] ?? 'continuous',
        'buzzer_duration' => $_POST['buzzer_duration'] ?? '2000',
        // Detection / False Positive
        'pir_sensitivity' => $_POST['pir_sensitivity'] ?? 'medium',
        'detection_cooldown' => $_POST['detection_cooldown'] ?? '30',
        'motion_confirm_count' => $_POST['motion_confirm_count'] ?? '1',
        'confidence_threshold' => $_POST['confidence_threshold'] ?? '50',
        'day_night_profile' => $_POST['day_night_profile'] ?? 'auto',
        'alert_delay' => $_POST['alert_delay'] ?? '0',
        'alert_trigger_mode' => $_POST['alert_trigger_mode'] ?? 'both',
    ];

    foreach ($settingsToUpdate as $key => $value) {
        update_setting($pdo, $key, $value);
    }

    log_audit($pdo, 'Settings Updated');
    $successMessage = "Settings saved successfully.";
}

// Load current settings
$s = get_all_settings($pdo);

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if (isset($successMessage)): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= h($successMessage) ?></div>
<?php endif; ?>

<!-- Settings Tabs -->
<div class="settings-tabs" id="settingsTabs">
    <button class="settings-tab active" data-tab="sms"><i class="fa-solid fa-comment-sms"></i> SMS (Beem Africa)</button>
    <button class="settings-tab" data-tab="alerts"><i class="fa-solid fa-bell"></i> Alert Config</button>
    <button class="settings-tab" data-tab="detection"><i class="fa-solid fa-radar"></i> Detection</button>
    <button class="settings-tab" data-tab="buzzer"><i class="fa-solid fa-volume-high"></i> Device Sound</button>
    <button class="settings-tab" data-tab="email"><i class="fa-solid fa-envelope"></i> Email (SMTP)</button>
</div>

<form method="POST" action="settings.php" id="settingsForm">
    <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">

    <!-- SMS Tab -->
    <div class="settings-panel active" data-panel="sms">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-comment-sms"></i> Beem Africa SMS Configuration</h2>
                <div class="toggle-switch-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="sms_enabled" value="1" <?= ($s['sms_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span>Enable SMS</span>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="sms_api_key"><i class="fa-solid fa-key"></i> API Key</label>
                    <input type="text" id="sms_api_key" name="sms_api_key" value="<?= h($s['sms_api_key'] ?? '') ?>" placeholder="Your Beem Africa API Key">
                </div>
                <div class="form-group">
                    <label for="sms_secret_key"><i class="fa-solid fa-lock"></i> Secret Key</label>
                    <input type="password" id="sms_secret_key" name="sms_secret_key" value="<?= h($s['sms_secret_key'] ?? '') ?>" placeholder="Your Beem Africa Secret Key">
                </div>
                <div class="form-group">
                    <label for="sms_sender_name"><i class="fa-solid fa-id-card"></i> Sender Name</label>
                    <input type="text" id="sms_sender_name" name="sms_sender_name" value="<?= h($s['sms_sender_name'] ?? 'INFO') ?>" placeholder="e.g. IOTSEC">
                </div>
                <div class="form-group">
                    <label for="sms_recipient"><i class="fa-solid fa-phone"></i> Recipient Phone(s)</label>
                    <input type="text" id="sms_recipient" name="sms_recipient" value="<?= h($s['sms_recipient'] ?? '') ?>" placeholder="e.g. 255712345678">
                    <small class="form-hint">Comma-separated for multiple. Use international format without +</small>
                </div>
            </div>

            <!-- Test SMS & Balance -->
            <div class="sms-actions">
                <div class="sms-action-group">
                    <button type="button" class="btn btn-outline" id="testSmsBtn" onclick="testSMS()">
                        <i class="fa-solid fa-paper-plane"></i> Send Test SMS
                    </button>
                    <button type="button" class="btn btn-outline" id="checkBalanceBtn" onclick="checkSmsBalance()">
                        <i class="fa-solid fa-coins"></i> Check Balance
                    </button>
                </div>
                <div id="smsBalanceDisplay" class="balance-display" style="display:none;">
                    <i class="fa-solid fa-coins"></i>
                    <span id="smsBalanceValue">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Config Tab -->
    <div class="settings-panel" data-panel="alerts">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-bell"></i> Alert Configuration</h2>
            </div>
            <div class="form-grid">
                <div class="form-group form-group-full">
                    <label for="alert_trigger_mode"><i class="fa-solid fa-filter"></i> Alert Trigger Mode</label>
                    <select id="alert_trigger_mode" name="alert_trigger_mode">
                        <option value="both" <?= ($s['alert_trigger_mode'] ?? '') === 'both' ? 'selected' : '' ?>>Both PIR and Laser</option>
                        <option value="pir_only" <?= ($s['alert_trigger_mode'] ?? '') === 'pir_only' ? 'selected' : '' ?>>PIR Motion Only</option>
                        <option value="camera_only" <?= ($s['alert_trigger_mode'] ?? '') === 'camera_only' ? 'selected' : '' ?>>Camera/Laser Only</option>
                        <option value="high_confidence" <?= ($s['alert_trigger_mode'] ?? '') === 'high_confidence' ? 'selected' : '' ?>>High Confidence Only (≥80%)</option>
                        <option value="critical" <?= ($s['alert_trigger_mode'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical Only (≥95%)</option>
                    </select>
                    <small class="form-hint">Controls when SMS notifications are sent</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detection Tab -->
    <div class="settings-panel" data-panel="detection">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-sliders"></i> False Positive Reduction Settings</h2>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="pir_sensitivity"><i class="fa-solid fa-gauge"></i> PIR Sensitivity</label>
                    <select id="pir_sensitivity" name="pir_sensitivity">
                        <option value="low" <?= ($s['pir_sensitivity'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                        <option value="medium" <?= ($s['pir_sensitivity'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                        <option value="high" <?= ($s['pir_sensitivity'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="detection_cooldown"><i class="fa-solid fa-hourglass-half"></i> Detection Cooldown (seconds)</label>
                    <input type="number" id="detection_cooldown" name="detection_cooldown" value="<?= h($s['detection_cooldown'] ?? '30') ?>" min="0" max="3600">
                    <small class="form-hint">Minimum time between alerts from the same device</small>
                </div>
                <div class="form-group">
                    <label for="motion_confirm_count"><i class="fa-solid fa-check-double"></i> Motion Confirmation Count</label>
                    <input type="number" id="motion_confirm_count" name="motion_confirm_count" value="<?= h($s['motion_confirm_count'] ?? '1') ?>" min="1" max="10">
                    <small class="form-hint">Number of detections required before triggering alert</small>
                </div>
                <div class="form-group">
                    <label for="confidence_threshold"><i class="fa-solid fa-percent"></i> Confidence Threshold (%)</label>
                    <input type="range" id="confidence_threshold" name="confidence_threshold" value="<?= h($s['confidence_threshold'] ?? '50') ?>" min="0" max="100" oninput="document.getElementById('confidenceValue').textContent = this.value + '%'">
                    <span class="range-value" id="confidenceValue"><?= h($s['confidence_threshold'] ?? '50') ?>%</span>
                </div>
                <div class="form-group">
                    <label for="day_night_profile"><i class="fa-solid fa-sun"></i> Day/Night Profile</label>
                    <select id="day_night_profile" name="day_night_profile">
                        <option value="auto" <?= ($s['day_night_profile'] ?? '') === 'auto' ? 'selected' : '' ?>>Auto</option>
                        <option value="day" <?= ($s['day_night_profile'] ?? '') === 'day' ? 'selected' : '' ?>>Day Mode</option>
                        <option value="night" <?= ($s['day_night_profile'] ?? '') === 'night' ? 'selected' : '' ?>>Night Mode</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="alert_delay"><i class="fa-solid fa-stopwatch"></i> Alert Delay (seconds)</label>
                    <input type="number" id="alert_delay" name="alert_delay" value="<?= h($s['alert_delay'] ?? '0') ?>" min="0" max="60">
                    <small class="form-hint">Delay before sending notification after detection</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Buzzer Tab -->
    <div class="settings-panel" data-panel="buzzer">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-volume-high"></i> Device Sound Configuration</h2>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="buzzer_mode_pir"><i class="fa-solid fa-person-running"></i> Motion (PIR) Sound Mode</label>
                    <select id="buzzer_mode_pir" name="buzzer_mode_pir">
                        <option value="beep" <?= ($s['buzzer_mode_pir'] ?? '') === 'beep' ? 'selected' : '' ?>>Beep Pattern</option>
                        <option value="continuous" <?= ($s['buzzer_mode_pir'] ?? '') === 'continuous' ? 'selected' : '' ?>>Continuous</option>
                        <option value="once" <?= ($s['buzzer_mode_pir'] ?? '') === 'once' ? 'selected' : '' ?>>Single Beep</option>
                        <option value="silent" <?= ($s['buzzer_mode_pir'] ?? '') === 'silent' ? 'selected' : '' ?>>Silent</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="buzzer_mode_laser"><i class="fa-solid fa-bolt"></i> Laser Beam Sound Mode</label>
                    <select id="buzzer_mode_laser" name="buzzer_mode_laser">
                        <option value="continuous" <?= ($s['buzzer_mode_laser'] ?? '') === 'continuous' ? 'selected' : '' ?>>Continuous</option>
                        <option value="beep" <?= ($s['buzzer_mode_laser'] ?? '') === 'beep' ? 'selected' : '' ?>>Beep Pattern</option>
                        <option value="once" <?= ($s['buzzer_mode_laser'] ?? '') === 'once' ? 'selected' : '' ?>>Single Beep</option>
                        <option value="silent" <?= ($s['buzzer_mode_laser'] ?? '') === 'silent' ? 'selected' : '' ?>>Silent</option>
                    </select>
                </div>
                <div class="form-group form-group-full">
                    <label for="buzzer_duration"><i class="fa-solid fa-clock"></i> Sound Duration (ms)</label>
                    <input type="number" id="buzzer_duration" name="buzzer_duration" value="<?= h($s['buzzer_duration'] ?? '2000') ?>" min="100" max="10000">
                </div>
            </div>
        </div>
    </div>

    <!-- Email Tab -->
    <div class="settings-panel" data-panel="email">
        <div class="panel">
            <div class="panel-header">
                <h2><i class="fa-solid fa-envelope"></i> Email Settings (SMTP)</h2>
                <div class="toggle-switch-group">
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_enabled" value="1" <?= ($s['email_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle-slider"></span>
                    </label>
                    <span>Enable Email</span>
                </div>
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="smtp_host">SMTP Host</label>
                    <input type="text" id="smtp_host" name="smtp_host" value="<?= h($s['smtp_host'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_port">SMTP Port</label>
                    <input type="number" id="smtp_port" name="smtp_port" value="<?= h($s['smtp_port'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_username">Username</label>
                    <input type="text" id="smtp_username" name="smtp_username" value="<?= h($s['smtp_username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="smtp_password">Password</label>
                    <input type="password" id="smtp_password" name="smtp_password" value="<?= h($s['smtp_password'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email_sender">Sender Email</label>
                    <input type="email" id="email_sender" name="email_sender" value="<?= h($s['email_sender'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="email_sender_name">Sender Name</label>
                    <input type="text" id="email_sender_name" name="email_sender_name" value="<?= h($s['email_sender_name'] ?? '') ?>">
                </div>
                <div class="form-group form-group-full">
                    <label for="email_recipient">Recipient Email(s)</label>
                    <input type="text" id="email_recipient" name="email_recipient" value="<?= h($s['email_recipient'] ?? '') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- Save Button -->
    <div class="save-bar">
        <button type="submit" name="save_settings" class="btn btn-primary btn-lg">
            <i class="fa-solid fa-save"></i> Save All Settings
        </button>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
