<?php
// alerts.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

// Handle alert resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resolve_id'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $stmt = $pdo->prepare("UPDATE alerts SET status = 'Resolved' WHERE id = ?");
    $stmt->execute([$_POST['resolve_id']]);
    log_system_action($pdo, "Alert " . $_POST['resolve_id'] . " Resolved", $_SESSION['user_id']);
    header("Location: alerts.php");
    exit();
}

$search = $_GET['search'] ?? '';
$where = '';
$params = [];
if ($search) {
    $where = "WHERE alert_type LIKE ? OR sensor_source LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT * FROM alerts $where ORDER BY id DESC");
$stmt->execute($params);
$alerts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts - IoT Security System</title>
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
                <li class="active"><a href="alerts.php"><i class="fa-solid fa-bell"></i> Alerts</a></li>
                <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </nav>
        
        <main class="main-content">
            <header class="topbar">
                <h1>All Alerts</h1>
            </header>

            <div class="panel">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search alerts..." value="<?= h($search) ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                
                <table class="table mt-20">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Sensor</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alerts as $alert): ?>
                        <tr>
                            <td><?= h($alert['id']) ?></td>
                            <td><?= h($alert['created_at']) ?></td>
                            <td><?= h($alert['alert_type']) ?></td>
                            <td><?= h($alert['sensor_source']) ?></td>
                            <td>
                                <?php if ($alert['status'] == 'Resolved'): ?>
                                    <span class="badge badge-success">Resolved</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Unresolved</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($alert['status'] != 'Resolved'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
                                    <input type="hidden" name="resolve_id" value="<?= h($alert['id']) ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Resolve</button>
                                </form>
                                <?php else: ?>
                                -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
