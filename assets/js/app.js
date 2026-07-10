document.addEventListener('DOMContentLoaded', function () {
    // Mobile sidebar toggle
    var shell = document.getElementById('appShell');
    var toggle = document.getElementById('sidebarToggle');
    var overlay = document.getElementById('sidebarOverlay');

    function closeSidebar() {
        if (shell) {
            shell.classList.remove('sidebar-open');
        }
    }

    if (toggle && shell) {
        toggle.addEventListener('click', function () {
            shell.classList.toggle('sidebar-open');
        });
    }
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSidebar();
        }
    });

    // Auto-dismiss success flash messages after a few seconds
    document.querySelectorAll('[data-autodismiss]').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });

    // Confirmation for destructive actions
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!window.confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
