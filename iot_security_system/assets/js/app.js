// assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initSidebar();
    initTabs();
    initDashboardCharts();
});

// --- Theme Management ---
function initTheme() {
    const themeToggle = document.getElementById('themeToggle');
    if (!themeToggle) return;

    const currentTheme = localStorage.getItem('theme') || 'dark';
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);

    themeToggle.addEventListener('click', () => {
        let theme = document.documentElement.getAttribute('data-theme');
        let newTheme = theme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
        
        // Update charts if they exist
        if (window.alertChart) window.alertChart.update();
        if (window.detectionChart) window.detectionChart.update();
    });
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#themeToggle i');
    if (!icon) return;
    if (theme === 'dark') {
        icon.className = 'fa-solid fa-sun';
    } else {
        icon.className = 'fa-solid fa-moon';
    }
}

// --- Sidebar Management ---
function initSidebar() {
    const hamburger = document.getElementById('hamburgerBtn');
    const closeBtn = document.getElementById('sidebarClose');
    const sidebar = document.getElementById('sidebar');

    if (hamburger && sidebar) {
        hamburger.addEventListener('click', () => sidebar.classList.add('open'));
    }
    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', () => sidebar.classList.remove('open'));
    }

    // Close on click outside (mobile)
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('open')) {
            if (!sidebar.contains(e.target) && e.target !== hamburger && !hamburger.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        }
    });
}

// --- Settings Tabs ---
function initTabs() {
    const tabs = document.querySelectorAll('.settings-tab');
    if (!tabs.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', (e) => {
            e.preventDefault();
            // Remove active from all
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            
            // Add active to clicked
            tab.classList.add('active');
            const targetPanel = tab.getAttribute('data-tab');
            document.querySelector(`.settings-panel[data-panel="${targetPanel}"]`).classList.add('active');
        });
    });
}

// --- Toast Notifications ---
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-circle-exclamation';
    toast.innerHTML = `<i class="fa-solid ${icon}"></i> <span>${message}</span>`;
    
    container.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 3s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// --- AJAX SMS Functions (Settings Page) ---
async function testSMS() {
    const recipient = document.getElementById('sms_recipient').value;
    if (!recipient) {
        showToast('Please enter a recipient phone number first.', 'error');
        return;
    }

    const btn = document.getElementById('testSmsBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
    btn.disabled = true;

    try {
        const response = await fetch('api/sms/test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ recipient, message: 'Test message from IoT Security System' }),
            credentials: 'same-origin'
        });
        const data = await response.json();
        
        if (data.status === 'success') showToast(data.message, 'success');
        else showToast(data.message, 'error');
    } catch (e) {
        showToast('Network error while sending test SMS', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

async function checkSmsBalance() {
    const btn = document.getElementById('checkBalanceBtn');
    const display = document.getElementById('smsBalanceDisplay');
    const valueSpan = document.getElementById('smsBalanceValue');
    
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';
    btn.disabled = true;

    try {
        const response = await fetch('api/sms/balance.php', { credentials: 'same-origin' });
        const data = await response.json();
        
        display.style.display = 'flex';
        if (data.status === 'success') {
            valueSpan.textContent = data.balance + ' TZS'; // Beem returns in TZS usually
            showToast('Balance fetched successfully', 'success');
        } else {
            valueSpan.textContent = 'Error';
            showToast(data.message, 'error');
        }
    } catch (e) {
        display.style.display = 'flex';
        valueSpan.textContent = 'Error';
        showToast('Network error fetching balance', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// --- Dashboard Real-time Polling & Charts ---
function getChartColors() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        textColor: isDark ? '#94a3b8' : '#6b7280',
        gridColor: isDark ? '#2e364f' : '#e5e7eb',
        primary: '#6366f1',
        warning: '#f59e0b',
        danger: '#ef4444'
    };
}

async function initDashboardCharts() {
    const alertCanvas = document.getElementById('alertChart');
    if (!alertCanvas) return; // Not on dashboard page

    // Fetch initial data
    try {
        const response = await fetch('api/dashboard_data.php', { credentials: 'same-origin' });
        const data = await response.json();
        
        if (data.status === 'success') {
            renderCharts(data);
            
            // Start polling every 10 seconds
            setInterval(pollDashboardData, 10000);
        }
    } catch (e) {
        console.error("Failed to load dashboard data", e);
    }
}

function renderCharts(data) {
    const colors = getChartColors();
    
    // Alert Trend Chart (Line)
    const ctxTrend = document.getElementById('alertChart').getContext('2d');
    
    // Prepare data
    const labels = data.weekly_trend.map(d => d.label);
    const motionData = data.weekly_trend.map(d => d.motion);
    const laserData = data.weekly_trend.map(d => d.laser);

    window.alertChart = new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Motion (PIR)',
                    data: motionData,
                    borderColor: colors.warning,
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Beam Break (Laser)',
                    data: laserData,
                    borderColor: colors.danger,
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: colors.textColor, usePointStyle: true } }
            },
            scales: {
                y: { grid: { color: colors.gridColor }, ticks: { color: colors.textColor, stepSize: 1 } },
                x: { grid: { display: false }, ticks: { color: colors.textColor } }
            }
        }
    });

    // Detection Chart (Doughnut)
    const ctxDet = document.getElementById('detectionChart').getContext('2d');
    window.detectionChart = new Chart(ctxDet, {
        type: 'doughnut',
        data: {
            labels: ['Motion', 'Beam Break'],
            datasets: [{
                data: [data.stats.total_motion, data.stats.total_beam_breaks],
                backgroundColor: [colors.warning, colors.danger],
                borderWidth: 0,
                cutout: '70%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: colors.textColor, usePointStyle: true } }
            }
        }
    });
}

async function pollDashboardData() {
    try {
        const response = await fetch('api/dashboard_data.php', { credentials: 'same-origin' });
        const data = await response.json();
        
        if (data.status === 'success') {
            // Update Stat Cards
            updateElementText('stat-devices .stat-value', data.stats.total_devices);
            updateElementText('stat-online .stat-value', data.stats.online_devices);
            updateElementText('stat-offline .stat-value', data.stats.offline_devices);
            updateElementText('stat-alerts .stat-value', data.stats.alerts_today);
            updateElementText('stat-sms .stat-value', data.stats.sms_sent_today);
            updateElementText('stat-unresolved .stat-value', data.stats.unresolved_alerts);
            
            // Update Charts
            if (window.alertChart) {
                window.alertChart.data.datasets[0].data = data.weekly_trend.map(d => d.motion);
                window.alertChart.data.datasets[1].data = data.weekly_trend.map(d => d.laser);
                window.alertChart.update('none'); // Update without animation
            }
            if (window.detectionChart) {
                window.detectionChart.data.datasets[0].data = [data.stats.total_motion, data.stats.total_beam_breaks];
                window.detectionChart.update('none');
            }
        }
    } catch (e) {
        // Silent fail for polling to avoid console spam
    }
}

function updateElementText(selector, text) {
    const el = document.querySelector(`#${selector}`);
    if (el && el.textContent != text) {
        el.textContent = text;
        // Small animation on update
        el.style.transform = 'scale(1.1)';
        el.style.color = 'var(--primary)';
        setTimeout(() => {
            el.style.transform = 'scale(1)';
            el.style.color = '';
        }, 300);
    }
}
