<?php
// includes/sidebar.php
$user = get_user($pdo, $_SESSION['user_id'] ?? 0);
$userName = $user['full_name'] ?? $user['username'] ?? 'Admin';
$userRole = $user['role'] ?? 'admin';
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <div class="brand-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="brand-text">
                <h2>IoT Security</h2>
                <span class="brand-tagline">Monitoring Platform</span>
            </div>
        </div>
        <button class="sidebar-close" id="sidebarClose" aria-label="Close sidebar">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-links">
            <li class="nav-section-title">Main</li>
            <li class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                <a href="dashboard.php"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            </li>
            <li class="<?= $currentPage === 'devices' ? 'active' : '' ?>">
                <a href="devices.php"><i class="fa-solid fa-microchip"></i><span>Devices</span></a>
            </li>
            <li class="<?= $currentPage === 'alerts' ? 'active' : '' ?>">
                <a href="alerts.php"><i class="fa-solid fa-bell"></i><span>Alerts</span></a>
            </li>

            <li class="nav-section-title">Configuration</li>
            <li class="<?= $currentPage === 'sms_templates' ? 'active' : '' ?>">
                <a href="sms_templates.php"><i class="fa-solid fa-message"></i><span>SMS Templates</span></a>
            </li>
            <li class="<?= $currentPage === 'settings' ? 'active' : '' ?>">
                <a href="settings.php"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
            </li>

            <li class="nav-section-title">System</li>
            <li class="<?= $currentPage === 'audit_logs' ? 'active' : '' ?>">
                <a href="audit_logs.php"><i class="fa-solid fa-clock-rotate-left"></i><span>Audit Logs</span></a>
            </li>
            <li class="<?= $currentPage === 'profile' ? 'active' : '' ?>">
                <a href="profile.php"><i class="fa-solid fa-user-gear"></i><span>Profile</span></a>
            </li>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="user-info">
                <span class="user-name"><?= h($userName) ?></span>
                <span class="user-role"><?= h(ucfirst($userRole)) ?></span>
            </div>
            <a href="logout.php" class="logout-btn" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </a>
        </div>
    </div>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <div class="topbar-left">
            <button class="hamburger" id="hamburgerBtn" aria-label="Toggle sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <h1 class="page-title"><?= h($pageTitle) ?></h1>
        </div>
        <div class="topbar-right">
            <button class="theme-toggle" id="themeToggle" title="Toggle theme">
                <i class="fa-solid fa-moon"></i>
            </button>
            <div class="topbar-user">
                <span><?= h($userName) ?></span>
                <div class="user-avatar-sm">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div>
        </div>
    </header>
    <main class="main-content">
