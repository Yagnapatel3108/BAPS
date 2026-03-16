<?php
// sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
$role_name    = $_SESSION['role_name'];
$full_name    = $_SESSION['full_name'];
$avatar_url   = 'https://ui-avatars.com/api/?name=' . urlencode($full_name) . '&background=f36f21&color=fff&bold=true&size=64';

$nav_items = [
    ['href' => 'dashboard.php', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard', 'roles' => null],
    ['href' => 'users.php',     'icon' => 'bi-people',        'label' => 'Users',     'roles' => ['Saint']],
    ['href' => 'mandals.php',   'icon' => 'bi-geo-alt',       'label' => 'Mandals',   'roles' => ['Saint']],
    ['href' => 'balaks.php',    'icon' => 'bi-person-heart',  'label' => 'Balaks',    'roles' => ['Saint','Nirdheshak','Agresar','Nirikshak','Karyakar','Sah-Karyakar']],
    ['href' => 'attendance.php','icon' => 'bi-calendar-check','label' => 'Attendance','roles' => ['Saint','Nirdheshak','Agresar','Nirikshak','Karyakar','Sah-Karyakar']],
    ['href' => 'mukhpath.php',  'icon' => 'bi-book',          'label' => 'Mukhpath',  'roles' => null],
    ['href' => 'sampark.php',   'icon' => 'bi-house-door',    'label' => 'Sampark',   'roles' => null],
    ['href' => 'announcements.php','icon' => 'bi-megaphone',  'label' => 'Announcements','roles' => null],
    ['href' => 'profile.php',   'icon' => 'bi-person-circle', 'label' => 'My Profile',   'roles' => null],
];
?>
<!-- Sidebar Backdrop for mobile -->
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<nav id="sidebar">
    <!-- Brand -->
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon">🙏</div>
        <div class="sidebar-brand-text">
            <h4>BAPS BPMS</h4>
            <small>Bal Pravrutti</small>
        </div>
    </div>

    <!-- Nav Links -->
    <div class="sidebar-nav">
        <div class="nav-label">Navigation</div>

        <?php foreach ($nav_items as $item):
            // Skip items the current role can't access
            if ($item['roles'] !== null && !in_array($role_name, $item['roles'])) continue;
            $is_active = ($current_page === $item['href']) ? 'active' : '';
        ?>
        <a href="<?php echo $item['href']; ?>" class="<?php echo $is_active; ?>">
            <i class="bi <?php echo $item['icon']; ?>"></i>
            <?php echo $item['label']; ?>
        </a>
        <?php endforeach; ?>

        <div class="nav-label" style="margin-top:12px;">Account</div>
        <a href="logout.php" class="<?php echo $current_page == 'logout.php' ? 'active' : ''; ?>" id="logoutLink">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>

    <!-- User footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <img src="<?php echo $avatar_url; ?>" alt="Avatar" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <h6><?php echo htmlspecialchars($full_name); ?></h6>
                <small><?php echo htmlspecialchars($role_name); ?></small>
            </div>
        </div>
    </div>
</nav>

<?php include 'logout_modal.php'; ?>

<script>
    // Logout confirmation via custom modal
    document.getElementById('logoutLink').addEventListener('click', function(e) {
        e.preventDefault();
        if (typeof openModal === 'function') {
            openModal('logoutModal');
        } else {
            // Fallback if script.js hasn't loaded helper for some reason
            document.getElementById('logoutModal').classList.add('active');
        }
    });
</script>
