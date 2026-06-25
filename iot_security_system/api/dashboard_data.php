<?php
// api/dashboard_data.php
require_once '../config/database.php';
require_once '../config/functions.php';

header("Content-Type: application/json");

// Require login via session (for AJAX calls from dashboard)
if (!is_logged_in()) {
    json_response(["status" => "error", "message" => "Unauthorized"], 401);
}

// Device counts
$totalDevices = get_count($pdo, "SELECT COUNT(*) FROM devices");
$onlineDevices = get_count($pdo, "SELECT COUNT(*) FROM devices WHERE status = 'Online' AND last_communication > DATE_SUB(NOW(), INTERVAL 60 SECOND)");
$offlineDevices = $totalDevices - $onlineDevices;

// Alert counts
$alertsToday = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE DATE(created_at) = CURDATE() AND status != 'Ignored'");
$unresolvedAlerts = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE status = 'Unresolved'");

// SMS counts
$smsSentToday = get_count($pdo, "SELECT COUNT(*) FROM sms_logs WHERE DATE(created_at) = CURDATE() AND status = 'Success'");

// Detection stats
$totalMotion = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE sensor_source = 'PIR'");
$totalBeamBreaks = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE sensor_source = 'Laser'");

// Weekly alert trend (last 7 days)
$weeklyData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayName = date('D', strtotime("-$i days"));
    
    $motionCount = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE sensor_source = 'PIR' AND DATE(created_at) = ?", [$date]);
    $laserCount = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE sensor_source = 'Laser' AND DATE(created_at) = ?", [$date]);
    
    $weeklyData[] = [
        'label' => $dayName,
        'date' => $date,
        'motion' => $motionCount,
        'laser' => $laserCount,
    ];
}

// Recent alerts (last 10)
$stmt = $pdo->query("SELECT a.*, d.device_name FROM alerts a LEFT JOIN devices d ON a.device_id = d.device_id ORDER BY a.id DESC LIMIT 10");
$recentAlerts = $stmt->fetchAll();

// All devices with status
$stmt = $pdo->query("SELECT * FROM devices ORDER BY last_communication DESC");
$devices = $stmt->fetchAll();

// Mark devices as offline if no communication in 60 seconds
foreach ($devices as &$dev) {
    $lastComm = strtotime($dev['last_communication']);
    if (time() - $lastComm > 60) {
        $dev['status'] = 'Offline';
    }
}

json_response([
    "status" => "success",
    "stats" => [
        "total_devices" => $totalDevices,
        "online_devices" => $onlineDevices,
        "offline_devices" => $offlineDevices,
        "alerts_today" => $alertsToday,
        "unresolved_alerts" => $unresolvedAlerts,
        "sms_sent_today" => $smsSentToday,
        "total_motion" => $totalMotion,
        "total_beam_breaks" => $totalBeamBreaks,
    ],
    "weekly_trend" => $weeklyData,
    "recent_alerts" => $recentAlerts,
    "devices" => $devices,
]);
?>
