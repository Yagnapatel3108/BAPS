<?php
// header.php — Top navigation bar
$full_name  = $_SESSION['full_name'];
$role_name  = $_SESSION['role_name'];
$avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=f36f21&color=fff&bold=true&size=64';

// Page title from each page
$page_titles = [
    'dashboard.php'    => 'Dashboard',
    'users.php'        => 'User Management',
    'balaks.php'       => 'Balaks',
    'attendance.php'   => 'Attendance',
    'attendance_report.php' => 'Attendance Report',
    'sampark.php'      => 'Sampark (Home Visits)',
    'announcements.php'=> 'Announcements',
    'profile.php'      => 'My Profile',
];
$current  = basename($_SERVER['PHP_SELF']);
$pg_title = $page_titles[$current] ?? 'BAPS BPMS';
?>
<nav class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <span class="topbar-title"><?php echo $pg_title; ?></span>
    </div>

    <div class="topbar-right">
        <!-- Notifications (decorative) -->
        <a href="#" class="topbar-badge" title="Notifications">
            <i class="bi bi-bell"></i>
        </a>

        <!-- Profile dropdown -->
        <div class="dropdown" id="profileDropdown">
            <div class="topbar-avatar-btn" id="profileBtn">
                <img src="<?php echo $avatar_url; ?>" alt="Avatar">
                <span><?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?></span>
                <i class="bi bi-chevron-down" style="font-size:11px; color:var(--text-muted);"></i>
            </div>
            <div class="dropdown-menu-custom">
                <div style="padding:10px 12px 6px;">
                    <p style="font-size:13px;font-weight:700;color:var(--text-primary);margin:0;"><?php echo htmlspecialchars($full_name); ?></p>
                    <p style="font-size:12px;color:var(--primary-light);margin:2px 0 0;"><?php echo htmlspecialchars($role_name); ?></p>
                </div>
                <div class="dropdown-divider"></div>
                <a href="profile.php"><i class="bi bi-person-circle"></i> My Profile</a>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="danger" id="headerLogoutLink"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>

<script>
// Profile dropdown toggle
(function(){
    const btn = document.getElementById('profileBtn');
    const dd  = document.getElementById('profileDropdown');
    if (btn && dd) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            dd.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            dd.classList.remove('open');
        });
    }

    // Sidebar toggle
    const toggle   = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
            if (backdrop) backdrop.classList.toggle('active');
        });
        if (backdrop) {
            backdrop.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-open');
                backdrop.classList.remove('active');
            });
        }
    }

    // Logout confirmation in header via custom modal
    const logoutLink = document.getElementById('headerLogoutLink');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof openModal === 'function') {
                openModal('logoutModal');
            } else {
                document.getElementById('logoutModal').classList.add('active');
            }
        });
    }
})();
</script>
