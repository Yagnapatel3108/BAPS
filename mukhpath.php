<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$success = $error = '';

// Handle add progress
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $balak_id  = (int)($_POST['balak_id'] ?? 0);
    $item_name = trim($_POST['item_name'] ?? '');
    $status    = $_POST['status'] ?? 'Pending';

    if ($balak_id && $item_name) {
        $stmt = $pdo->prepare("INSERT INTO mukhpath_progress (balak_id, item_name, status) VALUES (?,?,?)");
        $stmt->execute([$balak_id, $item_name, $status]);
        $success = "Progress recorded successfully!";
    } else {
        $error = "Balak selection and item name are required.";
    }
}

// Handle status update
if (isset($_GET['update_id']) && isset($_GET['status'])) {
    $update_id = (int)$_GET['update_id'];
    $new_status = $_GET['status'];
    if (in_array($new_status, ['Pending', 'In Progress', 'Completed'])) {
        $stmt = $pdo->prepare("UPDATE mukhpath_progress SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $update_id]);
        $success = "Status updated!";
    }
}

$progress = $pdo->query("
    SELECT p.*, b.full_name as balak_name 
    FROM mukhpath_progress p 
    JOIN balaks b ON p.balak_id = b.id 
    ORDER BY p.updated_at DESC
")->fetchAll();

$balaks = $pdo->query("SELECT id, full_name FROM balaks ORDER BY full_name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mukhpath Progress — BAPS Bal Pravrutti</title>
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
                    <h2>📖 Mukhpath Progress</h2>
                    <p>Track memorization progress for prayers and scriptures</p>
                </div>
                <button class="btn-primary-custom" onclick="openModal('addMukhpathModal')">
                    <i class="bi bi-journal-plus"></i> Add New Entry
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
                    <h5><i class="bi bi-book me-2" style="color:var(--primary-light)"></i>Classroom Progress</h5>
                </div>
                <div style="padding:0;">
                    <table class="table-dark-custom">
                        <thead>
                            <tr>
                                <th>Balak Name</th>
                                <th>Item (Subject)</th>
                                <th>Status</th>
                                <th style="text-align:right;">Update Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($progress)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:48px;color:var(--text-muted);">No progress records found.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($progress as $p): ?>
                            <tr>
                                <td><span class="fw-600"><?php echo htmlspecialchars($p['balak_name']); ?></span></td>
                                <td><?php echo htmlspecialchars($p['item_name']); ?></td>
                                <td>
                                    <?php 
                                    $cls = 'badge-blue';
                                    if($p['status'] === 'Completed') $cls = 'badge-green';
                                    if($p['status'] === 'Pending') $cls = 'badge-orange';
                                    ?>
                                    <span class="badge-custom <?php echo $cls; ?>"><?php echo $p['status']; ?></span>
                                </td>
                                <td style="text-align:right;">
                                    <div class="dropdown-custom" style="display:inline-block;">
                                        <button class="text-primary-custom" style="background:none;border:none;font-size:12px;font-weight:700;" onclick="toggleStatusDropdown(<?php echo $p['id']; ?>)">
                                            Change <i class="bi bi-chevron-down"></i>
                                        </button>
                                        <div id="drop-<?php echo $p['id']; ?>" class="dropdown-content-custom" style="right:0; top:24px; min-width:120px;">
                                            <a href="?update_id=<?php echo $p['id']; ?>&status=Pending">Pending</a>
                                            <a href="?update_id=<?php echo $p['id']; ?>&status=In Progress">In Progress</a>
                                            <a href="?update_id=<?php echo $p['id']; ?>&status=Completed">Completed</a>
                                        </div>
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

<div class="modal-overlay" id="addMukhpathModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>Assign Mukhpath Item</h5>
            <button class="modal-close" onclick="closeModal('addMukhpathModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group-dark">
                    <label class="form-label-dark">Select Balak</label>
                    <select name="balak_id" class="form-control-dark" required>
                        <option value="">Choose Balak…</option>
                        <?php foreach ($balaks as $b): ?>
                        <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Item Name (e.g. Daily Puja, Vachanamrut)</label>
                    <input type="text" name="item_name" class="form-control-dark" placeholder="Enter item name" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Initial Status</label>
                    <select name="status" class="form-control-dark">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('addMukhpathModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom">Assign Item</button>
            </div>
        </form>
    </div>
</div>

<style>
.dropdown-custom { position: relative; }
.dropdown-content-custom {
    display: none; position: absolute; background: var(--card-bg);
    border: 1px solid var(--card-border); border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5); z-index: 100;
}
.dropdown-content-custom.show { display: block; }
.dropdown-content-custom a {
    display: block; padding: 8px 16px; color: var(--text-primary);
    text-decoration: none; font-size: 13px; transition: 0.2s;
}
.dropdown-content-custom a:hover { background: rgba(255,255,255,0.05); color: var(--primary-light); }
</style>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function toggleStatusDropdown(id) {
    document.querySelectorAll('.dropdown-content-custom').forEach(d => {
        if(d.id !== 'drop-'+id) d.classList.remove('show');
    });
    document.getElementById('drop-'+id).classList.toggle('show');
}
window.onclick = function(event) {
    if (!event.target.matches('.text-primary-custom') && !event.target.matches('.bi-chevron-down')) {
        document.querySelectorAll('.dropdown-content-custom').forEach(d => d.classList.remove('show'));
    }
}
</script>
</body>
</html>
