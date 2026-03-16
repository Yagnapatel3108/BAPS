<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

// Real stats
$total_mandals   = $pdo->query("SELECT COUNT(*) FROM mandals")->fetchColumn();
$total_balaks    = $pdo->query("SELECT COUNT(*) FROM balaks")->fetchColumn();
$total_users     = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_sampark   = $pdo->query("SELECT COUNT(*) FROM sampark")->fetchColumn();

// Attendance this week
$week_present  = $pdo->query("SELECT COUNT(*) FROM attendance WHERE status='Present' AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$week_total    = $pdo->query("SELECT COUNT(*) FROM attendance WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$avg_attendance = $week_total > 0 ? round(($week_present / $week_total) * 100) : 0;

// Mukhpath
$mukh_completed = $pdo->query("SELECT COUNT(*) FROM mukhpath_progress WHERE status='Completed'")->fetchColumn();
$mukh_total     = $pdo->query("SELECT COUNT(*) FROM mukhpath_progress")->fetchColumn();
$mukh_pct       = $mukh_total > 0 ? round(($mukh_completed / $mukh_total) * 100) : 0;

// Attendance chart data (last 7 days)
$chart_labels  = [];
$chart_present = [];
$chart_absent  = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $label= date('D', strtotime($date));
    $p = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date=? AND status='Present'");
    $p->execute([$date]); $pr = $p->fetchColumn();
    $a = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE attendance_date=? AND status='Absent'");
    $a->execute([$date]); $ab = $a->fetchColumn();
    $chart_labels[]  = $label;
    $chart_present[] = (int)$pr;
    $chart_absent[]  = (int)$ab;
}

// Recent balaks
$recent_balaks = $pdo->query("SELECT b.full_name, m.mandal_name, b.created_at FROM balaks b LEFT JOIN mandals m ON b.mandal_id=m.id ORDER BY b.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — BAPS Bal Pravrutti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="wrapper">
    <?php include 'sidebar.php'; ?>

    <div id="content">
        <?php include 'header.php'; ?>

        <div class="main-content">

            <!-- Page Header -->
            <div class="page-header">
                <h2>👋 Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['full_name'])[0]); ?>!</h2>
                <p>Here's what's happening with Bal Pravrutti today — <?php echo date('l, d F Y'); ?></p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card orange">
                    <div class="stat-icon"><i class="bi bi-building"></i></div>
                    <div class="stat-value"><?php echo $total_mandals; ?></div>
                    <div class="stat-label">Total Mandals</div>
                    <div class="stat-trend text-muted-custom">
                        <i class="bi bi-geo-alt"></i> Active locations
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-icon"><i class="bi bi-person-heart"></i></div>
                    <div class="stat-value"><?php echo $total_balaks; ?></div>
                    <div class="stat-label">Total Balaks</div>
                    <div class="stat-trend text-success-custom">
                        <i class="bi bi-arrow-up"></i> Registered members
                    </div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="bi bi-calendar-check"></i></div>
                    <div class="stat-value"><?php echo $avg_attendance; ?>%</div>
                    <div class="stat-label">Weekly Attendance</div>
                    <div class="progress-bar-wrap" style="margin-top:12px;">
                        <div class="progress-bar-fill" style="width:<?php echo $avg_attendance; ?>%; background: linear-gradient(90deg,#0284c7,#38bdf8);"></div>
                    </div>
                </div>
                <div class="stat-card yellow">
                    <div class="stat-icon"><i class="bi bi-book"></i></div>
                    <div class="stat-value"><?php echo $mukh_pct; ?>%</div>
                    <div class="stat-label">Mukhpath Progress</div>
                    <div class="progress-bar-wrap" style="margin-top:12px;">
                        <div class="progress-bar-fill" style="width:<?php echo $mukh_pct; ?>%; background: linear-gradient(90deg,#ca8a04,#eab308);"></div>
                    </div>
                </div>
            </div>

            <!-- Charts + Recent Balaks -->
            <div class="grid-2 mb-28">

                <!-- Attendance Chart -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-bar-chart-line me-2" style="color:var(--info)"></i>7-Day Attendance</h5>
                        <span class="badge-custom badge-blue fs-12">This Week</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrap">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Summary Donut -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-pie-chart me-2" style="color:var(--primary-light)"></i>System Overview</h5>
                        <span class="badge-custom badge-orange fs-12">Live</span>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrap">
                            <canvas id="overviewChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <?php if (checkRole(['Saint'], false)): ?>
            <div class="card">
                <div class="card-header"><h5><i class="bi bi-lightning-charge me-2" style="color:var(--primary-light)"></i>Quick Actions</h5></div>
                <div class="card-body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <button class="btn-primary-custom" style="padding:10px;" onclick="openModal('addBalakModal')"><i class="bi bi-person-plus"></i> Balak</button>
                        <a href="announcements.php" class="btn-secondary-custom" style="padding:10px;text-align:center;text-decoration:none;"><i class="bi bi-megaphone"></i> Post</a>
                        <a href="mandals.php" class="btn-secondary-custom" style="padding:10px;text-align:center;text-decoration:none;grid-column: span 2;background:rgba(243,111,33,0.1);border-color:rgba(243,111,33,0.3);"><i class="bi bi-geo-alt"></i> Manage Mandals (Mandir)</a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Balaks Table -->
            <div class="card mb-28">
                <div class="card-header">
                    <h5><i class="bi bi-clock-history me-2" style="color:var(--success)"></i>Recently Added Balaks</h5>
                    <a href="balaks.php" class="btn-primary-custom btn-sm-custom">View All</a>
                </div>
                <div class="card-body" style="padding:0;">
                    <table class="table-dark-custom w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mandal</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_balaks)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color:var(--text-muted); padding:32px;">
                                    <i class="bi bi-inbox" style="font-size:24px;display:block;margin-bottom:8px;"></i>
                                    No balaks registered yet.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($recent_balaks as $i => $b): ?>
                            <tr>
                                <td><span class="text-muted-custom"><?php echo $i + 1; ?></span></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:32px;height:32px;border-radius:50%;background:rgba(243,111,33,0.15);display:flex;align-items:center;justify-content:center;color:var(--primary-light);font-weight:700;font-size:13px;">
                                            <?php echo strtoupper(substr($b['full_name'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($b['full_name']); ?>
                                    </div>
                                </td>
                                <td><span class="badge-custom badge-blue"><?php echo htmlspecialchars($b['mandal_name'] ?? '—'); ?></span></td>
                                <td><span class="text-muted-custom fs-13"><?php echo date('d M Y', strtotime($b['created_at'])); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-lightning me-2" style="color:var(--warning)"></i>Quick Actions</h5>
                </div>
                <div class="card-body" style="display:flex;flex-wrap:wrap;gap:12px;">
                    <?php if (in_array($role_name, ['Saint','Nirdheshak','Agresar','Nirikshak','Karyakar','Sah-Karyakar'])): ?>
                    <a href="balaks.php" class="btn-primary-custom"><i class="bi bi-person-plus"></i> Add Balak</a>
                    <a href="attendance.php" class="btn-secondary-custom"><i class="bi bi-calendar-plus"></i> Mark Attendance</a>
                    <?php endif; ?>
                    <a href="sampark.php" class="btn-secondary-custom"><i class="bi bi-house-door"></i> Record Sampark</a>
                    <?php if ($role_name === 'Saint'): ?>
                    <a href="announcements.php" class="btn-secondary-custom"><i class="bi bi-megaphone"></i> New Announcement</a>
                    <a href="users.php" class="btn-secondary-custom"><i class="bi bi-people"></i> Manage Users</a>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /main-content -->
    </div><!-- /content -->
</div><!-- /wrapper -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart: Attendance
(function(){
    const ctx = document.getElementById('attendanceChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [
                {
                    label: 'Present',
                    data: <?php echo json_encode($chart_present); ?>,
                    backgroundColor: 'rgba(34,197,94,0.7)',
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Absent',
                    data: <?php echo json_encode($chart_absent); ?>,
                    backgroundColor: 'rgba(239,68,68,0.5)',
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#7a8394', font: { family: 'Inter', size: 12 } } },
                tooltip: { callbacks: {} }
            },
            scales: {
                x: { ticks: { color: '#7a8394' }, grid: { color: 'rgba(255,255,255,0.04)' } },
                y: { ticks: { color: '#7a8394', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
            }
        }
    });
})();

// Chart: Overview donut
(function(){
    const ctx = document.getElementById('overviewChart');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Balaks', 'Users', 'Mandals', 'Sampark Visits'],
            datasets: [{
                data: [<?php echo $total_balaks; ?>, <?php echo $total_users; ?>, <?php echo $total_mandals; ?>, <?php echo $total_sampark; ?>],
                backgroundColor: [
                    'rgba(243,111,33,0.8)',
                    'rgba(34,197,94,0.8)',
                    'rgba(56,189,248,0.8)',
                    'rgba(234,179,8,0.8)'
                ],
                borderColor: '#1a1f2e',
                borderWidth: 3,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#7a8394', font: { family: 'Inter', size: 12 }, padding: 16 }
                }
            }
        }
    });
})();
</script>
</body>
</html>
