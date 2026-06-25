<?php
// devices.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

$pageTitle = 'Devices';
$currentPage = 'devices';

// Handle device add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_device'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $deviceId = trim($_POST['device_id'] ?? '');
    $deviceName = trim($_POST['device_name'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if (!empty($deviceId)) {
        $stmt = $pdo->prepare("INSERT INTO devices (device_id, device_name, location) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE device_name = VALUES(device_name), location = VALUES(location)");
        $stmt->execute([$deviceId, $deviceName ?: $deviceId, $location]);
        log_audit($pdo, 'Device Added', "Device: $deviceId");
    }
    header("Location: devices.php");
    exit();
}

// Handle device edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_device'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = $_POST['id'] ?? '';
    $deviceName = trim($_POST['device_name'] ?? '');
    $location = trim($_POST['location'] ?? '');

    $stmt = $pdo->prepare("UPDATE devices SET device_name = ?, location = ? WHERE id = ?");
    $stmt->execute([$deviceName, $location, $id]);
    log_audit($pdo, 'Device Updated', "Device ID: $id");
    header("Location: devices.php");
    exit();
}

// Handle device delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_device'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = $_POST['id'] ?? '';
    $stmt = $pdo->prepare("SELECT device_id FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    $devId = $stmt->fetchColumn();
    $pdo->prepare("DELETE FROM devices WHERE id = ?")->execute([$id]);
    log_audit($pdo, 'Device Deleted', "Device: $devId");
    header("Location: devices.php");
    exit();
}

// Handle activate/deactivate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_active'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $id = $_POST['id'] ?? '';
    $stmt = $pdo->prepare("SELECT is_active, device_id FROM devices WHERE id = ?");
    $stmt->execute([$id]);
    $dev = $stmt->fetch();
    if ($dev) {
        $newState = $dev['is_active'] ? 0 : 1;
        $pdo->prepare("UPDATE devices SET is_active = ? WHERE id = ?")->execute([$newState, $id]);
        log_audit($pdo, $newState ? 'Device Activated' : 'Device Deactivated', "Device: " . $dev['device_id']);
    }
    header("Location: devices.php");
    exit();
}

// Fetch all devices
$stmt = $pdo->query("SELECT * FROM devices ORDER BY last_communication DESC");
$devices = $stmt->fetchAll();

foreach ($devices as &$dev) {
    $lastComm = strtotime($dev['last_communication']);
    $dev['computed_status'] = (time() - $lastComm < 60) ? 'Online' : 'Offline';
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<!-- Actions Bar -->
<div class="actions-bar">
    <div class="actions-left">
        <span class="text-muted"><?= count($devices) ?> device(s) registered</span>
    </div>
    <div class="actions-right">
        <button class="btn btn-primary" id="addDeviceBtn" onclick="document.getElementById('addDeviceModal').classList.add('modal-open')">
            <i class="fa-solid fa-plus"></i> Add Device
        </button>
    </div>
</div>

<!-- Devices Grid -->
<?php if (empty($devices)): ?>
    <div class="panel">
        <div class="empty-state">
            <i class="fa-solid fa-microchip"></i>
            <h3>No devices registered</h3>
            <p>Add your first IoT device to get started.</p>
        </div>
    </div>
<?php else: ?>
<div class="devices-grid">
    <?php foreach ($devices as $dev): ?>
    <div class="device-card <?= !$dev['is_active'] ? 'device-inactive' : '' ?>">
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
                <span class="info-value"><?= h($dev['location'] ?: 'Not set') ?></span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-code-branch"></i> Firmware</span>
                <span class="info-value"><?= h($dev['firmware_version'] ?? 'Unknown') ?></span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-shield-halved"></i> Mode</span>
                <span class="info-value">
                    <span class="mode-badge mode-<?= strtolower($dev['security_mode']) ?>"><?= h($dev['security_mode']) ?></span>
                </span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-person-running"></i> PIR</span>
                <span class="info-value <?= $dev['pir_status'] ? 'text-danger' : '' ?>"><?= $dev['pir_status'] ? 'Motion' : 'Clear' ?></span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-bolt"></i> Laser</span>
                <span class="info-value <?= $dev['laser_status'] ? 'text-danger' : '' ?>"><?= $dev['laser_status'] ? 'Broken' : 'Intact' ?></span>
            </div>
            <div class="device-info-row">
                <span class="info-label"><i class="fa-solid fa-clock"></i> Last Seen</span>
                <span class="info-value"><?= format_time_ago($dev['last_communication']) ?></span>
            </div>
        </div>
        <div class="device-card-footer">
            <div class="device-actions">
                <button class="btn btn-outline btn-xs" onclick="openEditModal(<?= htmlspecialchars(json_encode($dev), ENT_QUOTES) ?>)">
                    <i class="fa-solid fa-pen"></i> Edit
                </button>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= $dev['id'] ?>">
                    <button type="submit" name="toggle_active" class="btn btn-xs <?= $dev['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                        <i class="fa-solid <?= $dev['is_active'] ? 'fa-pause' : 'fa-play' ?>"></i>
                        <?= $dev['is_active'] ? 'Deactivate' : 'Activate' ?>
                    </button>
                </form>
                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this device?')">
                    <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= $dev['id'] ?>">
                    <button type="submit" name="delete_device" class="btn btn-danger btn-xs">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Add Device Modal -->
<div class="modal-overlay" id="addDeviceModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-plus"></i> Add New Device</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('modal-open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
            <div class="modal-body">
                <div class="form-group">
                    <label for="add_device_id">Device ID</label>
                    <input type="text" id="add_device_id" name="device_id" placeholder="e.g. ESP32_NODE_02" required>
                </div>
                <div class="form-group">
                    <label for="add_device_name">Device Name</label>
                    <input type="text" id="add_device_name" name="device_name" placeholder="e.g. Garage Sensor">
                </div>
                <div class="form-group">
                    <label for="add_location">Location</label>
                    <input type="text" id="add_location" name="location" placeholder="e.g. Back Gate">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('modal-open')">Cancel</button>
                <button type="submit" name="add_device" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Device</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Device Modal -->
<div class="modal-overlay" id="editDeviceModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> Edit Device</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('modal-open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
            <input type="hidden" name="id" id="edit_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Device ID</label>
                    <input type="text" id="edit_device_id_display" disabled>
                </div>
                <div class="form-group">
                    <label for="edit_device_name">Device Name</label>
                    <input type="text" id="edit_device_name" name="device_name" required>
                </div>
                <div class="form-group">
                    <label for="edit_location">Location</label>
                    <input type="text" id="edit_location" name="location">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="this.closest('.modal-overlay').classList.remove('modal-open')">Cancel</button>
                <button type="submit" name="edit_device" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(device) {
    document.getElementById('edit_id').value = device.id;
    document.getElementById('edit_device_id_display').value = device.device_id;
    document.getElementById('edit_device_name').value = device.device_name || '';
    document.getElementById('edit_location').value = device.location || '';
    document.getElementById('editDeviceModal').classList.add('modal-open');
}
</script>

<?php include 'includes/footer.php'; ?>
