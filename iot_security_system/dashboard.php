<?php
// dashboard.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

// Fetch summary stats
$totalDevices = get_count($pdo, "SELECT COUNT(*) FROM devices");
$alertsToday = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE DATE(created_at) = CURDATE() AND status != 'Ignored'");
$totalMotion = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE sensor_source = 'PIR'");
$totalBeamBreaks = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE sensor_source = 'Laser'");
$smsSentToday = get_count($pdo, "SELECT COUNT(*) FROM sms_logs WHERE DATE(created_at) = CURDATE() AND status = 'Success'");
$unresolvedAlerts = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE status = 'Unresolved'");

// Fetch all devices
$stmt = $pdo->query("SELECT * FROM devices ORDER BY last_communication DESC");
$devices = $stmt->fetchAll();

$onlineCount = 0;
foreach ($devices as &$dev) {
    $lastComm = strtotime($dev['last_communication']);
    if (time() - $lastComm < 60) {
        $dev['computed_status'] = 'Online';
        $onlineCount++;
    } else {
        $dev['computed_status'] = 'Offline';
    }
}
$offlineCount = $totalDevices - $onlineCount;

// Recent alerts
$stmt = $pdo->query("SELECT a.*, d.device_name FROM alerts a LEFT JOIN devices d ON a.device_id = d.device_id ORDER BY a.id DESC LIMIT 8");
$recentAlerts = $stmt->fetchAll();

// Handle mode toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_mode'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $deviceId = $_POST['device_id'] ?? '';
    $stmt = $pdo->prepare("SELECT security_mode FROM devices WHERE device_id = ?");
    $stmt->execute([$deviceId]);
    $device = $stmt->fetch();
    if ($device) {
        $newMode = ($device['security_mode'] === 'Armed') ? 'Disarmed' : 'Armed';
        $stmt = $pdo->prepare("UPDATE devices SET security_mode = ? WHERE device_id = ?");
        $stmt->execute([$newMode, $deviceId]);
        log_audit($pdo, "Device Mode Changed", "Device $deviceId → $newMode");
    }
    header("Location: dashboard.php");
    exit();
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card" id="stat-devices">
        <div class="stat-icon gradient-blue"><i class="fa-solid fa-microchip"></i></div>
        <div class="stat-content">
            <span class="stat-label">Total Devices</span>
            <span class="stat-value"><?= $totalDevices ?></span>
        </div>
    </div>
    <div class="stat-card" id="stat-online">
        <div class="stat-icon gradient-green"><i class="fa-solid fa-wifi"></i></div>
        <div class="stat-content">
            <span class="stat-label">Online</span>
            <span class="stat-value"><?= $onlineCount ?></span>
        </div>
    </div>
    <div class="stat-card" id="stat-offline">
        <div class="stat-icon gradient-red"><i class="fa-solid fa-plug-circle-xmark"></i></div>
        <div class="stat-content">
            <span class="stat-label">Offline</span>
            <span class="stat-value"><?= $offlineCount ?></span>
        </div>
    </div>
    <div class="stat-card" id="stat-alerts">
        <div class="stat-icon gradient-orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
        <div class="stat-content">
            <span class="stat-label">Alerts Today</span>
            <span class="stat-value"><?= $alertsToday ?></span>
        </div>
    </div>
    <div class="stat-card" id="stat-sms">
        <div class="stat-icon gradient-purple"><i class="fa-solid fa-comment-sms"></i></div>
        <div class="stat-content">
            <span class="stat-label">SMS Sent Today</span>
            <span class="stat-value"><?= $smsSentToday ?></span>
        </div>
    </div>
    <div class="stat-card" id="stat-unresolved">
        <div class="stat-icon gradient-yellow"><i class="fa-solid fa-clock"></i></div>
        <div class="stat-content">
            <span class="stat-label">Unresolved</span>
            <span class="stat-value"><?= $unresolvedAlerts ?></span>
        </div>
    </div>
</div>

<!-- Devices Grid -->
<div class="section-header">
    <h2><i class="fa-solid fa-microchip"></i> Device Status</h2>
    <a href="devices.php" class="btn btn-outline btn-sm">View All</a>
</div>
<div class="devices-grid">
    <?php foreach ($devices as $dev): ?>
    <div class="device-card">
        <div class="device-card-header">
            <div class="device-name-group">
                <h3><?= h($dev['device_name'] ?? $dev['device_id']) ?></h3>
                <span class="device-id"><?= h($dev['device_id']) ?></span>
            </div>
            <span class="status-badge <?= $dev['computed_status'] === 'Online' ? 'status-online' : 'status-offline' ?>">
                <span class="status-dot"></span>
                <?= $dev['computed_status'] ?>
            </span>
        </div>
        <div class="device-card-body">
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-location-dot"></i> Location</span>
                <span class="info-value"><?= h($dev['location'] ?? 'Not set') ?></span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-person-running"></i> PIR</span>
                <span class="info-value <?= $dev['pir_status'] ? 'text-danger' : '' ?>">
                    <?= $dev['pir_status'] ? 'Motion Detected' : 'Clear' ?>
                </span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-bolt"></i> Laser</span>
                <span class="info-value <?= $dev['laser_status'] ? 'text-danger' : '' ?>">
                    <?= $dev['laser_status'] ? 'Beam Broken' : 'Intact' ?>
                </span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-sun"></i> LDR</span>
                <span class="info-value"><?= h($dev['ldr_value']) ?></span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-clock"></i> Last Seen</span>
                <span class="info-value"><?= format_time_ago($dev['last_communication']) ?></span>
            </div>
        </div>
        <div class="device-card-footer">
            <span class="mode-badge mode-<?= strtolower($dev['security_mode']) ?>">
                <i class="fa-solid fa-shield-halved"></i> <?= h($dev['security_mode']) ?>
            </span>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                <input type="hidden" name="device_id" value="<?= h($dev['device_id']) ?>">
                <button type="submit" name="toggle_mode" class="btn btn-sm <?= $dev['security_mode'] === 'Armed' ? 'btn-warning' : 'btn-success' ?>">
                    <?= $dev['security_mode'] === 'Armed' ? 'Disarm' : 'Arm' ?>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Charts + Recent Alerts Row -->
<div class="dashboard-grid-2col">
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-chart-line"></i> Alert Trend (7 Days)</h2>
        </div>
        <div class="chart-container">
            <canvas id="alertChart"></canvas>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-bell"></i> Recent Alerts</h2>
            <a href="alerts.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="alerts-list">
            <?php if (empty($recentAlerts)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-check-circle"></i>
                    <p>No recent alerts</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentAlerts as $alert): ?>
                <div class="alert-item">
                    <div class="alert-item-icon <?= $alert['sensor_source'] === 'PIR' ? 'alert-motion' : 'alert-laser' ?>">
                        <i class="fa-solid <?= $alert['sensor_source'] === 'PIR' ? 'fa-person-running' : 'fa-bolt' ?>"></i>
                    </div>
                    <div class="alert-item-content">
                        <span class="alert-item-type"><?= h($alert['alert_type']) ?></span>
                        <span class="alert-item-meta">
                            <?= h($alert['device_name'] ?? $alert['device_id']) ?> · <?= format_time_ago($alert['created_at']) ?>
                        </span>
                    </div>
                    <span class="status-badge status-<?= strtolower($alert['status']) ?>">
                        <?= h($alert['status']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Detection Stats -->
<div class="dashboard-grid-2col">
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-chart-pie"></i> Detection Breakdown</h2>
        </div>
        <div class="chart-container chart-container-sm">
            <canvas id="detectionChart"></canvas>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-signal"></i> Detection Stats</h2>
        </div>
        <div class="stats-summary">
            <div class="summary-item">
                <span class="summary-label">Total Motion Detections</span>
                <span class="summary-value"><?= $totalMotion ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Total Beam Breaks</span>
                <span class="summary-value"><?= $totalBeamBreaks ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">SMS Notifications Sent</span>
                <span class="summary-value"><?= $smsSentToday ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Active Devices</span>
                <span class="summary-value"><?= $onlineCount ?>/<?= $totalDevices ?></span>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
