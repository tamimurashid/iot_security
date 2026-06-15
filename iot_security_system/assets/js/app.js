// assets/js/app.js

document.addEventListener("DOMContentLoaded", () => {
    // If we're on the dashboard, render the chart and start polling
    const ctx = document.getElementById('alertChart');
    if (ctx) {
        renderChart(ctx);
        
        // Poll for updates every 10 seconds
        setInterval(() => {
            // A real app would fetch new data here via fetch('/api/device/status.php')
            // For now, we rely on the PHP page refresh or specific AJAX calls.
            // window.location.reload(); 
        }, 10000);
    }
});

function renderChart(ctx) {
    // Mock data - in a real app, you would fetch this from the PHP backend via an API endpoint.
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            datasets: [
                {
                    label: 'Motion Alerts',
                    data: [2, 5, 1, 0, 8, 3, 4],
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Beam Breaks',
                    data: [0, 1, 0, 0, 2, 1, 0],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}
