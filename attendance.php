<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$success = $error = '';
$today = date('Y-m-d');
$selected_date = $_GET['date'] ?? $today;

$balaks  = $pdo->query("SELECT b.*, m.mandal_name FROM balaks b LEFT JOIN mandals m ON b.mandal_id=m.id ORDER BY m.mandal_name, b.full_name")->fetchAll();

// Get already-marked attendance for selected date
$marked = [];
$stmt   = $pdo->prepare("SELECT balak_id, status FROM attendance WHERE attendance_date = ?");
$stmt->execute([$selected_date]);
foreach ($stmt->fetchAll() as $row) {
    $marked[$row['balak_id']] = $row['status'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance — BAPS Bal Pravrutti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .att-toggle { display: flex; gap: 0; border-radius: 8px; overflow: hidden; }
        .att-toggle input[type=radio] { display: none; }
        .att-toggle label {
            flex: 1; padding: 7px 18px; cursor: pointer; font-size: 13px; font-weight: 600;
            text-align: center; transition: all 0.2s; border: 1.5px solid var(--card-border);
            background: var(--input-bg); color: var(--text-muted);
        }
        .att-toggle label:first-of-type { border-radius: 8px 0 0 8px; border-right: none; }
        .att-toggle label:last-of-type  { border-radius: 0 8px 8px 0; }
        .att-toggle input[value=Present]:checked + label { background: rgba(34,197,94,0.2); color: #22c55e; border-color: rgba(34,197,94,0.4); }
        .att-toggle input[value=Absent]:checked  + label { background: rgba(239,68,68,0.2);  color: #ef4444; border-color: rgba(239,68,68,0.4);  }
        .mandal-group-header {
            background: rgba(243,111,33,0.08);
            border-left: 3px solid var(--primary);
            padding: 10px 16px;
            font-size: 12px; font-weight: 700; color: var(--primary-light);
            letter-spacing: 0.6px; text-transform: uppercase;
        }
        .att-counter { font-size: 14px; font-weight: 700; }
    </style>
</head>
<body>
<div id="wrapper">
    <?php include 'sidebar.php'; ?>
    <div id="content">
        <?php include 'header.php'; ?>
        <div class="main-content">

            <div class="page-header flex items-center justify-between">
                <div>
                    <h2>📋 Mark Attendance</h2>
                    <p>Record attendance for Bal Pravrutti Sabha sessions</p>
                </div>
                <a href="attendance_report.php" class="btn-secondary-custom">
                    <i class="bi bi-bar-chart-line"></i> View Reports
                </a>
            </div>

            <?php if ($success): ?>
            <div class="alert-custom alert-success"><i class="bi bi-check-circle"></i><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="save_attendance.php" id="attForm">
                <!-- Date selector + counters -->
                <div class="card mb-20">
                    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
                        <div style="display:flex;align-items:center;gap:14px;">
                            <div>
                                <label class="form-label-dark">Attendance Date</label>
                                <input type="date" name="attendance_date" id="dateInput" value="<?php echo htmlspecialchars($selected_date); ?>"
                                    class="form-control-dark" style="width:auto;"
                                    onchange="window.location='attendance.php?date='+this.value">
                            </div>
                            <div style="padding-left:14px;border-left:1px solid var(--card-border);">
                                <div class="fs-12 text-muted-custom mb-4-custom">Total Balaks</div>
                                <div class="att-counter text-primary-custom"><?php echo count($balaks); ?></div>
                            </div>
                        </div>
                        <div style="display:flex;gap:20px;">
                            <div style="text-align:center;">
                                <div class="fs-12 text-muted-custom mb-4-custom">Present</div>
                                <div class="att-counter" style="color:var(--success);" id="presentCount">0</div>
                            </div>
                            <div style="text-align:center;">
                                <div class="fs-12 text-muted-custom mb-4-custom">Absent</div>
                                <div class="att-counter" style="color:#ef4444;" id="absentCount">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (empty($balaks)): ?>
                <div class="card">
                    <div class="card-body" style="text-align:center;padding:56px 24px;">
                        <i class="bi bi-people" style="font-size:48px;color:var(--text-dim);display:block;margin-bottom:14px;"></i>
                        <p style="color:var(--text-muted);">No balaks registered. <a href="balaks.php" class="text-primary-custom">Register balaks first →</a></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="card">
                    <?php
                    $current_mandal = null;
                    foreach ($balaks as $balak):
                        if ($balak['mandal_name'] !== $current_mandal):
                            if ($current_mandal !== null) echo '</tbody>';
                            $current_mandal = $balak['mandal_name'];
                    ?>
                        <div class="mandal-group-header">
                            <i class="bi bi-geo-alt me-2"></i><?php echo htmlspecialchars($current_mandal ?? 'No Mandal'); ?>
                        </div>
                        <table class="table-dark-custom" style="border-bottom:1px solid var(--card-border);">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Balak Name</th>
                                <th style="text-align:center;">Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                    <?php endif; ?>
                        <tr>
                            <td class="text-muted-custom fs-13"><?php echo $balak['id']; ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(243,111,33,0.12);display:flex;align-items:center;justify-content:center;color:var(--primary-light);font-weight:700;font-size:12px;">
                                        <?php echo strtoupper(substr($balak['full_name'],0,1)); ?>
                                    </div>
                                    <span class="fw-600"><?php echo htmlspecialchars($balak['full_name']); ?></span>
                                </div>
                            </td>
                            <td style="text-align:center;">
                                <div class="att-toggle" style="justify-content:center;display:inline-flex;">
                                    <?php $status = $marked[$balak['id']] ?? 'Present'; ?>
                                    <input type="radio" name="status[<?php echo $balak['id']; ?>]" id="p<?php echo $balak['id']; ?>" value="Present" class="att-radio" <?php echo $status==='Present'?'checked':''; ?>>
                                    <label for="p<?php echo $balak['id']; ?>">✓ Present</label>
                                    <input type="radio" name="status[<?php echo $balak['id']; ?>]" id="a<?php echo $balak['id']; ?>" value="Absent"  class="att-radio" <?php echo $status==='Absent'?'checked':''; ?>>
                                    <label for="a<?php echo $balak['id']; ?>">✗ Absent</label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody></table>
                </div>

                <div style="margin-top:20px;display:flex;justify-content:flex-end;gap:12px;">
                    <button type="reset" class="btn-secondary-custom" onclick="updateCounts()">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <button type="submit" class="btn-primary-custom" style="padding:11px 32px;font-size:15px;">
                        <i class="bi bi-check2-circle"></i> Save Attendance
                    </button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script>
function updateCounts() {
    setTimeout(function(){
        const radios = document.querySelectorAll('.att-radio:checked');
        let p = 0, a = 0;
        radios.forEach(r => { if(r.value==='Present') p++; else a++; });
        document.getElementById('presentCount').textContent = p;
        document.getElementById('absentCount').textContent  = a;
    }, 50);
}
document.querySelectorAll('.att-radio').forEach(r => r.addEventListener('change', updateCounts));
updateCounts();
</script>
</body>
</html>
