<?php
// alerts.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

$pageTitle = 'Alerts';
$currentPage = 'alerts';

// Handle alert resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_id'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $stmt = $pdo->prepare("UPDATE alerts SET status = 'Resolved' WHERE id = ?");
    $stmt->execute([$_POST['resolve_id']]);
    log_audit($pdo, 'Alert Resolved', "Alert ID: " . $_POST['resolve_id']);
    header("Location: alerts.php");
    exit();
}

// Handle bulk resolve
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_resolve'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $pdo->exec("UPDATE alerts SET status = 'Resolved' WHERE status = 'Unresolved'");
    log_audit($pdo, 'Bulk Alert Resolution', 'All unresolved alerts resolved');
    header("Location: alerts.php");
    exit();
}

// Filters
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$sensorFilter = $_GET['sensor'] ?? 'all';
$deviceFilter = $_GET['device'] ?? 'all';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (a.alert_type LIKE ? OR a.sensor_source LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($statusFilter !== 'all') {
    $where .= " AND a.status = ?";
    $params[] = $statusFilter;
}
if ($sensorFilter !== 'all') {
    $where .= " AND a.sensor_source = ?";
    $params[] = $sensorFilter;
}
if ($deviceFilter !== 'all') {
    $where .= " AND a.device_id = ?";
    $params[] = $deviceFilter;
}

$stmt = $pdo->prepare("SELECT a.*, d.device_name FROM alerts a LEFT JOIN devices d ON a.device_id = d.device_id $where ORDER BY a.id DESC LIMIT 100");
$stmt->execute($params);
$alerts = $stmt->fetchAll();

// Get all devices for filter dropdown
$stmt = $pdo->query("SELECT device_id, device_name FROM devices ORDER BY device_name");
$allDevices = $stmt->fetchAll();

// Counts for tabs
$countAll = get_count($pdo, "SELECT COUNT(*) FROM alerts");
$countUnresolved = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE status = 'Unresolved'");
$countResolved = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE status = 'Resolved'");
$countIgnored = get_count($pdo, "SELECT COUNT(*) FROM alerts WHERE status = 'Ignored'");

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Filter Tabs -->
<div class="filter-tabs">
    <a href="alerts.php?status=all" class="filter-tab <?= $statusFilter === 'all' ? 'active' : '' ?>">
        All <span class="tab-count"><?= $countAll ?></span>
    </a>
    <a href="alerts.php?status=Unresolved" class="filter-tab <?= $statusFilter === 'Unresolved' ? 'active' : '' ?>">
        Unresolved <span class="tab-count"><?= $countUnresolved ?></span>
    </a>
    <a href="alerts.php?status=Resolved" class="filter-tab <?= $statusFilter === 'Resolved' ? 'active' : '' ?>">
        Resolved <span class="tab-count"><?= $countResolved ?></span>
    </a>
    <a href="alerts.php?status=Ignored" class="filter-tab <?= $statusFilter === 'Ignored' ? 'active' : '' ?>">
        Ignored <span class="tab-count"><?= $countIgnored ?></span>
    </a>
</div>

<!-- Search and Filters -->
<div class="panel">
    <form method="GET" class="filters-form" id="alertFilters">
        <div class="filters-row">
            <div class="filter-group">
                <input type="text" name="search" placeholder="Search alerts..." value="<?= h($search) ?>" class="filter-input">
            </div>
            <div class="filter-group">
                <select name="sensor" class="filter-select">
                    <option value="all" <?= $sensorFilter === 'all' ? 'selected' : '' ?>>All Sensors</option>
                    <option value="PIR" <?= $sensorFilter === 'PIR' ? 'selected' : '' ?>>PIR (Motion)</option>
                    <option value="Laser" <?= $sensorFilter === 'Laser' ? 'selected' : '' ?>>Laser (Beam Break)</option>
                </select>
            </div>
            <div class="filter-group">
                <select name="device" class="filter-select">
                    <option value="all" <?= $deviceFilter === 'all' ? 'selected' : '' ?>>All Devices</option>
                    <?php foreach ($allDevices as $d): ?>
                    <option value="<?= h($d['device_id']) ?>" <?= $deviceFilter === $d['device_id'] ? 'selected' : '' ?>>
                        <?= h($d['device_name'] ?? $d['device_id']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <input type="hidden" name="status" value="<?= h($statusFilter) ?>">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filter</button>
        </div>
    </form>
</div>

<!-- Bulk Actions -->
<?php if ($countUnresolved > 0): ?>
<div class="bulk-actions">
    <form method="POST" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
        <button type="submit" name="bulk_resolve" class="btn btn-success btn-sm" onclick="return confirm('Resolve all unresolved alerts?')">
            <i class="fa-solid fa-check-double"></i> Resolve All (<?= $countUnresolved ?>)
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Alerts Table -->
<div class="panel">
    <?php if (empty($alerts)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-check-circle"></i>
            <h3>No alerts found</h3>
            <p>No alerts match your current filters.</p>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table" id="alertsTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Time</th>
                    <th>Device</th>
                    <th>Type</th>
                    <th>Sensor</th>
                    <th>Confidence</th>
                    <th>SMS</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($alerts as $alert): ?>
                <tr>
                    <td><span class="text-muted">#<?= h($alert['id']) ?></span></td>
                    <td>
                        <span class="time-display"><?= format_time_ago($alert['created_at']) ?></span>
                        <span class="time-full"><?= h($alert['created_at']) ?></span>
                    </td>
                    <td><?= h($alert['device_name'] ?? $alert['device_id']) ?></td>
                    <td>
                        <span class="alert-type-badge">
                            <i class="fa-solid <?= strpos($alert['alert_type'], 'Motion') !== false ? 'fa-person-running' : 'fa-bolt' ?>"></i>
                            <?= h($alert['alert_type']) ?>
                        </span>
                    </td>
                    <td><?= h($alert['sensor_source']) ?></td>
                    <td>
                        <span class="confidence-bar">
                            <span class="confidence-fill" style="width: <?= $alert['confidence'] ?>%"></span>
                            <span class="confidence-text"><?= $alert['confidence'] ?>%</span>
                        </span>
                    </td>
                    <td>
                        <?php if ($alert['sms_sent']): ?>
                            <span class="badge badge-success"><i class="fa-solid fa-check"></i> Sent</span>
                        <?php else: ?>
                            <span class="badge badge-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $statusClass = match($alert['status']) {
                            'Resolved' => 'status-resolved',
                            'Unresolved' => 'status-unresolved',
                            'Ignored' => 'status-ignored',
                            'Cooldown' => 'status-cooldown',
                            default => ''
                        };
                        ?>
                        <span class="status-badge <?= $statusClass ?>"><?= h($alert['status']) ?></span>
                    </td>
                    <td>
                        <?php if ($alert['status'] === 'Unresolved'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                            <input type="hidden" name="resolve_id" value="<?= h($alert['id']) ?>">
                            <button type="submit" class="btn btn-success btn-xs"><i class="fa-solid fa-check"></i></button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
