<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$success = $error = '';

// Handle add balak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $full_name      = trim($_POST['full_name'] ?? '');
    $mandal_id      = (int)($_POST['mandal_id'] ?? 0);
    $contact_number = trim($_POST['contact_number'] ?? '');
    $dob            = $_POST['dob'] ?? '';
    $address        = trim($_POST['address'] ?? '');
    if ($full_name && $mandal_id) {
        $pdo->prepare("INSERT INTO balaks (full_name, mandal_id, contact_number, dob, address) VALUES (?,?,?,?,?)")
            ->execute([$full_name, $mandal_id, $contact_number ?: null, $dob ?: null, $address ?: null]);
        $success = "Balak registered successfully!";
    } else {
        $error = "Name and Mandal are required.";
    }
}

$balaks  = $pdo->query("SELECT b.*, m.mandal_name FROM balaks b LEFT JOIN mandals m ON b.mandal_id=m.id ORDER BY b.full_name ASC")->fetchAll();
$mandals = $pdo->query("SELECT * FROM mandals ORDER BY mandal_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balaks — BAPS Bal Pravrutti</title>
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
                    <h2>👦 Balak Management</h2>
                    <p><?php echo count($balaks); ?> Balaks registered in the system</p>
                </div>
                <button class="btn-primary-custom" onclick="openModal('addBalakModal')">
                    <i class="bi bi-person-plus"></i> Register Balak
                </button>
            </div>

            <?php if ($success): ?>
            <div class="alert-custom alert-success"><i class="bi bi-check-circle"></i><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <!-- Search bar -->
                <div class="card-header">
                    <h5><i class="bi bi-people me-2" style="color:var(--success)"></i>All Balaks</h5>
                    <div style="position:relative;">
                        <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);font-size:14px;"></i>
                        <input type="text" id="searchInput" class="form-control-dark" placeholder="Search balak…"
                            style="padding-left:36px;width:220px;" oninput="filterTable()">
                    </div>
                </div>
                <div style="padding:0;">
                    <table class="table-dark-custom" id="balaksTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mandal</th>
                                <th>Contact</th>
                                <th>Date of Birth</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($balaks)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:48px;color:var(--text-muted);">
                                    <i class="bi bi-person-x" style="font-size:36px;display:block;margin-bottom:10px;"></i>
                                    No balaks registered yet.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($balaks as $i => $b): ?>
                            <tr>
                                <td><span class="text-muted-custom"><?php echo $i + 1; ?></span></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <div style="width:34px;height:34px;border-radius:50%;background:rgba(243,111,33,0.15);display:flex;align-items:center;justify-content:center;color:var(--primary-light);font-weight:700;">
                                            <?php echo strtoupper(substr($b['full_name'],0,1)); ?>
                                        </div>
                                        <span class="fw-600"><?php echo htmlspecialchars($b['full_name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="badge-custom badge-blue"><?php echo htmlspecialchars($b['mandal_name'] ?? '—'); ?></span></td>
                                <td><span class="text-muted-custom"><?php echo htmlspecialchars($b['contact_number'] ?? '—'); ?></span></td>
                                <td><span class="text-muted-custom"><?php echo $b['dob'] ? date('d M Y', strtotime($b['dob'])) : '—'; ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Balak Modal -->
<div class="modal-overlay" id="addBalakModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-person-plus me-2" style="color:var(--success)"></i>Register New Balak</h5>
            <button class="modal-close" onclick="closeModal('addBalakModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group-dark">
                        <label class="form-label-dark">Full Name *</label>
                        <input type="text" name="full_name" class="form-control-dark" placeholder="Balak's full name" required>
                    </div>
                    <div class="form-group-dark">
                        <label class="form-label-dark">Mandal *</label>
                        <select name="mandal_id" class="form-control-dark" required>
                            <option value="">Select Mandal…</option>
                            <?php foreach ($mandals as $m): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['mandal_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group-dark">
                        <label class="form-label-dark">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control-dark" placeholder="Phone number">
                    </div>
                    <div class="form-group-dark">
                        <label class="form-label-dark">Date of Birth</label>
                        <input type="date" name="dob" class="form-control-dark">
                    </div>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Address</label>
                    <textarea name="address" class="form-control-dark" rows="2" placeholder="Home address…" style="resize:vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('addBalakModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-person-check"></i> Save Balak</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', function(e) { if (e.target === m) m.classList.remove('active'); });
});
function filterTable() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#balaksTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
</body>
</html>
