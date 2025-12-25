document.addEventListener('DOMContentLoaded', function() {
    initializeTooltips();
    autoHideAlerts();
});

function initializeTooltips() {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
}

function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            new bootstrap.Alert(alert).close();
        }, 5000);
    });
}

function confirmDelete(message = 'Are you sure?') {
    return confirm(message);
}
