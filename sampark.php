<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$success = $error = '';

// Handle add sampark
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $balak_id   = (int)($_POST['balak_id'] ?? 0);
    $visit_date = $_POST['visit_date'] ?? date('Y-m-d');
    $remarks    = trim($_POST['remarks'] ?? '');
    $visited_by = $_SESSION['user_id'];

    if ($balak_id && $remarks) {
        $stmt = $pdo->prepare("INSERT INTO sampark (balak_id, visit_date, remarks, visited_by) VALUES (?,?,?,?)");
        $stmt->execute([$balak_id, $visit_date, $remarks, $visited_by]);
        $success = "Sampark record added successfully!";
    } else {
        $error = "Balak selection and remarks are required.";
    }
}

$samparks = $pdo->query("
    SELECT s.*, b.full_name as balak_name, u.full_name as visitor_name 
    FROM sampark s 
    JOIN balaks b ON s.balak_id = b.id 
    JOIN users u ON s.visited_by = u.id 
    ORDER BY s.visit_date DESC
")->fetchAll();

$balaks = $pdo->query("SELECT id, full_name FROM balaks ORDER BY full_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sampark (Home Visits) — BAPS Bal Pravrutti</title>
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
                    <h2>🏠 Sampark (Home Visits)</h2>
                    <p>Track personal follow-ups and home visits with Balaks</p>
                </div>
                <button class="btn-primary-custom" onclick="openModal('addSamparkModal')">
                    <i class="bi bi-plus-lg"></i> Record New Visit
                </button>
            </div>

            <?php if ($success): ?>
            <div class="alert-custom alert-success"><i class="bi bi-check-circle"></i><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-journal-text me-2" style="color:var(--primary-light)"></i>Recent Visits</h5>
                </div>
                <div style="padding:0;">
                    <table class="table-dark-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Balak</th>
                                <th>Remarks</th>
                                <th>Visited By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($samparks)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:48px;color:var(--text-muted);">No sampark records found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($samparks as $s): ?>
                            <tr>
                                <td><span class="text-primary-custom fw-600"><?php echo date('d M, Y', strtotime($s['visit_date'])); ?></span></td>
                                <td><span class="fw-600"><?php echo htmlspecialchars($s['balak_name']); ?></span></td>
                                <td><span class="text-muted-custom"><?php echo htmlspecialchars($s['remarks']); ?></span></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="width:24px;height:24px;border-radius:50%;background:rgba(255,140,66,0.1);display:flex;align-items:center;justify-content:center;font-size:10px;color:var(--primary-light);font-weight:700;">
                                            <?php echo strtoupper(substr($s['visitor_name'],0,1)); ?>
                                        </div>
                                        <span class="fs-13"><?php echo htmlspecialchars($s['visitor_name']); ?></span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal-overlay" id="addSamparkModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>Record Home Visit</h5>
            <button class="modal-close" onclick="closeModal('addSamparkModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group-dark">
                    <label class="form-label-dark">Select Balak</label>
                    <select name="balak_id" class="form-control-dark" required>
                        <option value="">Search Balak…</option>
                        <?php foreach ($balaks as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Visit Date</label>
                    <input type="date" name="visit_date" class="form-control-dark" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Remarks / Observation</label>
                    <textarea name="remarks" class="form-control-dark" rows="4" placeholder="How was the visit? Any specific feedback?" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('addSamparkModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom">Save Record</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
</script>
</body>
</html>
