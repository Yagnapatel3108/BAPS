// BAPS BPMS — Core JavaScript
document.addEventListener('DOMContentLoaded', function() {
    
    // Sidebar Mobile Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    if (sidebarToggle && sidebar && sidebarBackdrop) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarBackdrop.classList.toggle('active');
        });

        sidebarBackdrop.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarBackdrop.classList.remove('active');
        });
    }

    // Modal Global Handling
    window.openModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeModal = function(id) {
        const modal = document.getElementById(id);
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    };

    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });

    // Auto-hide Alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-custom');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

});
