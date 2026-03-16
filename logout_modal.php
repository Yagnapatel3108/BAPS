<!-- logout_modal.php -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box" style="max-width: 400px; text-align: center;">
        <div class="modal-header" style="justify-content: center; border-bottom: none; padding-top: 32px;">
            <div style="width: 64px; height: 64px; background: rgba(239,68,68,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: var(--danger); font-size: 28px; margin-bottom: 8px;">
                <i class="bi bi-box-arrow-right"></i>
            </div>
        </div>
        <div class="modal-body" style="padding: 0 32px 32px;">
            <h5 style="font-size: 20px; font-weight: 800; margin-bottom: 8px; color: var(--text-primary);">Confirm Logout</h5>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 0;">Are you sure you want to log out of your account?</p>
        </div>
        <div class="modal-footer" style="border-top: 1px solid var(--card-border); padding: 16px 24px; background: rgba(0,0,0,0.1); border-radius: 0 0 var(--radius-lg) var(--radius-lg);">
            <button type="button" class="btn-secondary-custom" onclick="closeModal('logoutModal')" style="flex: 1;">Cancel</button>
            <a href="logout.php" class="btn-primary-custom" style="flex: 1; background: var(--danger); justify-content: center;">Logout</a>
        </div>
    </div>
</div>

<script>
// Global Modal Helpers (if not already defined)
if (typeof window.openModal !== 'function') {
    window.openModal = function(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    };
}

if (typeof window.closeModal !== 'function') {
    window.closeModal = function(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('active');
            document.body.style.overflow = '';
        }
    };
}

// Close on overlay click
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === m) closeModal(m.id); });
});
</script>
