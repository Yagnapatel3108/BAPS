<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$date = $_GET['date'] ?? date('Y-m-d');
$mandal_id = $_GET['mandal_id'] ?? '';

$where = " WHERE a.attendance_date = ? ";
$params = [$date];

if ($mandal_id) {
    $where .= " AND b.mandal_id = ? ";
    $params[] = $mandal_id;
}

$stmt = $pdo->prepare("
    SELECT a.*, b.full_name as balak_name, m.mandal_name 
    FROM attendance a 
    JOIN balaks b ON a.balak_id = b.id 
    JOIN mandals m ON b.mandal_id = m.id 
    $where 
    ORDER BY m.mandal_name, b.full_name
");
$stmt->execute($params);
$attendance = $stmt->fetchAll();

$mandals = $pdo->query("SELECT * FROM mandals ORDER BY mandal_name")->fetchAll();

// Statistics
$total = count($attendance);
$present = 0;
foreach($attendance as $row) if($row['status'] === 'Present') $present++;
$absent = $total - $present;
$percent = $total > 0 ? round(($present / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Report — BAPS Bal Pravrutti</title>
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

            <div class="page-header flex items-center justify-between">
                <div>
                    <h2>📊 Attendance Report</h2>
                    <p>Detailed attendance statistics and history</p>
                </div>
                <div style="display:flex;gap:10px;">
                    <button class="btn-secondary-custom" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Report
                    </button>
                    <a href="attendance.php" class="btn-primary-custom">
                        <i class="bi bi-plus-lg"></i> Mark Attendance
                    </a>
                </div>
            </div>

            <div class="card mb-20">
                <div class="card-body">
                    <form method="GET" style="display:flex;gap:16px;align-items:flex-end;flex-wrap:wrap;">
                        <div class="form-group-dark mb-0-custom" style="flex:1;min-width:180px;">
                            <label class="form-label-dark">Select Date</label>
                            <input type="date" name="date" class="form-control-dark" value="<?php echo $date; ?>">
                        </div>
                        <div class="form-group-dark mb-0-custom" style="flex:1;min-width:180px;">
                            <label class="form-label-dark">Filter by Mandal</label>
                            <select name="mandal_id" class="form-control-dark">
                                <option value="">All Mandals</option>
                                <?php foreach($mandals as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php echo $mandal_id == $m['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m['mandal_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary-custom" style="height:44px;padding:0 24px;">Filter</button>
                    </form>
                </div>
            </div>

            <div class="grid-4 mb-20">
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(255,140,66,0.1);color:#ff8c42;"><i class="bi bi-people"></i></div>
                    <div class="stat-val"><?php echo $total; ?></div>
                    <div class="stat-label">Total Expected</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(34,197,94,0.1);color:#22c55e;"><i class="bi bi-check2-circle"></i></div>
                    <div class="stat-val"><?php echo $present; ?></div>
                    <div class="stat-label">Total Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(239,68,68,0.1);color:#ef4444;"><i class="bi bi-x-circle"></i></div>
                    <div class="stat-val"><?php echo $absent; ?></div>
                    <div class="stat-label">Total Absent</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:rgba(243,111,33,0.1);color:var(--primary-light);"><i class="bi bi-percent"></i></div>
                    <div class="stat-val"><?php echo $percent; ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>

            <div class="card">
                <div style="padding:0;">
                    <table class="table-dark-custom">
                        <thead>
                            <tr>
                                <th>Mandal</th>
                                <th>Balak Name</th>
                                <th>Status</th>
                                <th>Recorded At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($attendance)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:48px;color:var(--text-muted);">No attendance records found for this date.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($attendance as $a): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($a['mandal_name']); ?></td>
                                <td><span class="fw-600"><?php echo htmlspecialchars($a['balak_name']); ?></span></td>
                                <td>
                                    <span class="badge-custom <?php echo $a['status'] === 'Present' ? 'badge-green' : 'badge-orange'; ?>">
                                        <?php echo $a['status']; ?>
                                    </span>
                                </td>
                                <td><span class="text-muted-custom fs-12"><?php echo date('H:i A', strtotime($a['created_at'])); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
