<?php
// profile.php
require_once 'config/database.php';
require_once 'config/functions.php';
require_login();

$pageTitle = 'Profile';
$currentPage = 'profile';

$user = get_user($pdo, $_SESSION['user_id']);
$successMessage = '';
$errorMessage = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
    $stmt->execute([$fullName, $email, $_SESSION['user_id']]);
    log_audit($pdo, 'Profile Updated');
    $successMessage = 'Profile updated successfully.';
    $user = get_user($pdo, $_SESSION['user_id']);
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();

    if (!password_verify($currentPassword, $hash)) {
        $errorMessage = 'Current password is incorrect.';
    } elseif (strlen($newPassword) < 6) {
        $errorMessage = 'New password must be at least 6 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $errorMessage = 'New passwords do not match.';
    } else {
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $_SESSION['user_id']]);
        log_audit($pdo, 'Password Changed');
        $successMessage = 'Password changed successfully.';
    }
}

include 'includes/header.php';
include 'includes/sidebar.php';
?>

<?php if ($successMessage): ?>
    <div class="alert alert-success"><i class="fa-solid fa-check-circle"></i> <?= h($successMessage) ?></div>
<?php endif; ?>
<?php if ($errorMessage): ?>
    <div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> <?= h($errorMessage) ?></div>
<?php endif; ?>

<div class="dashboard-grid-2col">
    <!-- Profile Info -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-user"></i> Profile Information</h2>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?= h($user['username']) ?>" disabled>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= h($user['full_name'] ?? '') ?>" placeholder="Your full name">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= h($user['email'] ?? '') ?>" placeholder="your@email.com">
            </div>
            <div class="form-group">
                <label>Role</label>
                <input type="text" value="<?= h(ucfirst($user['role'] ?? 'admin')) ?>" disabled>
            </div>
            <div class="form-group">
                <label>Member Since</label>
                <input type="text" value="<?= h($user['created_at']) ?>" disabled>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">
                <i class="fa-solid fa-save"></i> Update Profile
            </button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-lock"></i> Change Password</h2>
        </div>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-warning">
                <i class="fa-solid fa-key"></i> Change Password
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
