<?php
// index.php — Login Page
require_once 'config/database.php';
require_once 'config/functions.php';

if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password, full_name FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        log_audit($pdo, 'User Login', "Username: $username", $user['id']);
        header("Location: dashboard.php");
        exit();
    } else {
        $error = 'Invalid username or password.';
        log_audit($pdo, 'Failed Login Attempt', "Username: $username");
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="IoT Security System - Secure Login">
    <title>Login — IoT Security</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <div class="login-bg">
        <div class="login-gradient-orb orb-1"></div>
        <div class="login-gradient-orb orb-2"></div>
        <div class="login-gradient-orb orb-3"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <div class="logo-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                </div>
                <h1>IoT Security</h1>
                <p class="login-subtitle">Monitoring Platform</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <?= h($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php" class="login-form" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= h(generate_csrf_token()) ?>">

                <div class="form-group">
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" id="username" name="username" placeholder="Username" required autocomplete="username">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-icon-wrapper">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Password" required autocomplete="current-password">
                        <button type="button" class="password-toggle" id="passwordToggle">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-login" id="loginBtn">
                    <span>Sign In</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </form>

            <div class="login-footer">
                <p>Secure IoT Monitoring System</p>
            </div>
        </div>
    </div>

    <script>
        // Password toggle
        document.getElementById('passwordToggle')?.addEventListener('click', function() {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
</body>
</html>
