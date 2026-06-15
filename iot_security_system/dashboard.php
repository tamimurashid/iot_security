<?php
// dashboard.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

// Fetch summary stats
$stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE DATE(created_at) = CURDATE()");
$totalIntrusionsToday = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE sensor_source = 'PIR'");
$totalMotionDetections = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM alerts WHERE sensor_source = 'Laser'");
$totalBeamBreaks = $stmt->fetchColumn();

// Fetch device status
$stmt = $pdo->query("SELECT * FROM devices WHERE device_id = 'ESP32_NODE_01'");
$device = $stmt->fetch();

// Check if device is offline (no comms in last 60 seconds)
$isOffline = true;
if ($device) {
    $lastComm = strtotime($device['last_communication']);
    if (time() - $lastComm < 60) {
        $isOffline = false;
    }
}
$statusClass = $isOffline ? 'danger' : 'success';
$statusText = $isOffline ? 'Offline' : 'Online';

// Handle mode toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_mode'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $newMode = ($device['security_mode'] === 'Armed') ? 'Disarmed' : 'Armed';
    $stmt = $pdo->prepare("UPDATE devices SET security_mode = ? WHERE device_id = 'ESP32_NODE_01'");
    $stmt->execute([$newMode]);
    log_system_action($pdo, "System Mode Changed to " . $newMode, $_SESSION['user_id']);
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IoT Security System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="wrapper">
        <nav class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-shield"></i> IoT Sec</h2>
            </div>
            <ul class="nav-links">
                <li class="active"><a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <li><a href="alerts.php"><i class="fa-solid fa-bell"></i> Alerts</a></li>
                <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="topbar">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>Admin</span>
                    <i class="fa-solid fa-user-circle"></i>
                </div>
            </header>

            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div class="card-info">
                        <h3>Total Intrusions (Today)</h3>
                        <p><?= h($totalIntrusionsToday) ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-person-running"></i></div>
                    <div class="card-info">
                        <h3>Total Motion Detections</h3>
                        <p><?= h($totalMotionDetections) ?></p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="card-info">
                        <h3>Total Beam Breaks</h3>
                        <p><?= h($totalBeamBreaks) ?></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="panel">
                    <h2>Device Status</h2>
                    <div class="status-indicator">
                        <span class="badge badge-<?= h($statusClass) ?>"><?= h($statusText) ?></span>
                        <p>Last Communication: <?= $device ? h($device['last_communication']) : 'Never' ?></p>
                    </div>
                    
                    <div class="sensor-status">
                        <p><strong>PIR Sensor:</strong> <?= ($device && $device['pir_status']) ? '<span class="text-danger">Motion Detected</span>' : 'Clear' ?></p>
                        <p><strong>Laser Module:</strong> <?= ($device && $device['laser_status']) ? '<span class="text-danger">Beam Broken</span>' : 'Intact' ?></p>
                        <p><strong>LDR Value:</strong> <?= $device ? h($device['ldr_value']) : 0 ?></p>
                    </div>

                    <div class="mode-control">
                        <h3>Current Mode: <strong><?= $device ? h($device['security_mode']) : 'Unknown' ?></strong></h3>
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                            <button type="submit" name="toggle_mode" class="btn btn-warning w-100">Toggle Security Mode</button>
                        </form>
                    </div>
                </div>
                
                <div class="panel">
                    <h2>Recent Alerts</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Type</th>
                                <th>Sensor</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM alerts ORDER BY id DESC LIMIT 5");
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?= h($row['created_at']) ?></td>
                                <td><?= h($row['alert_type']) ?></td>
                                <td><?= h($row['sensor_source']) ?></td>
                                <td><span class="badge badge-warning"><?= h($row['status']) ?></span></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <a href="alerts.php" class="btn btn-primary" style="margin-top:15px; display:inline-block;">View All Alerts</a>
                </div>
            </div>
            
            <div class="panel mt-20">
                <h2>Alert History</h2>
                <canvas id="alertChart"></canvas>
            </div>
        </main>
    </div>
    <script src="assets/js/app.js"></script>
</body>
</html>
