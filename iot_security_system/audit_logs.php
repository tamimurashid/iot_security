<?php
// audit_logs.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

$pageTitle = 'Audit Logs';
$currentPage = 'audit_logs';

$actionFilter = $_GET['action'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

$where = '';
$params = [];
if ($actionFilter) {
    $where = "WHERE action LIKE ?";
    $params[] = "%$actionFilter%";
}

$totalLogs = get_count($pdo, "SELECT COUNT(*) FROM audit_logs $where", $params);
$totalPages = ceil($totalLogs / $perPage);

$params[] = $perPage;
$params[] = $offset;
$stmt = $pdo->prepare("SELECT al.*, u.username FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id $where ORDER BY al.id DESC LIMIT ? OFFSET ?");
$stmt->execute($params);
$logs = $stmt->fetchAll();

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fa-solid fa-clock-rotate-left"></i> System Audit Logs</h2>
        <span class="text-muted"><?= $totalLogs ?> total entries</span>
    </div>

    <form method="GET" class="filters-form">
        <div class="filters-row">
            <div class="filter-group">
                <input type="text" name="action" placeholder="Filter by action..." value="<?= h($actionFilter) ?>" class="filter-input">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Filter</button>
            <?php if ($actionFilter): ?>
                <a href="audit_logs.php" class="btn btn-outline btn-sm">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($logs)): ?>
        <div class="empty-state"><i class="fa-solid fa-clock-rotate-left"></i><h3>No audit logs found</h3></div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <span class="time-display"><?= format_time_ago($log['created_at']) ?></span>
                        <span class="time-full"><?= h($log['created_at']) ?></span>
                    </td>
                    <td><span class="badge badge-muted"><?= h($log['username'] ?? 'System') ?></span></td>
                    <td><strong><?= h($log['action']) ?></strong></td>
                    <td><span class="text-muted"><?= h($log['details'] ?? '—') ?></span></td>
                    <td><code><?= h($log['ip_address'] ?? '—') ?></code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&action=<?= urlencode($actionFilter) ?>" class="btn btn-outline btn-sm">&laquo; Previous</a>
        <?php endif; ?>
        <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&action=<?= urlencode($actionFilter) ?>" class="btn btn-outline btn-sm">Next &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
