<?php
require_once 'config.php';
checkRole(['Saint']);

$success = $error = '';

// Handle add mandal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $mandal_name = trim($_POST['mandal_name'] ?? '');
    $karyakar_id = (int)($_POST['karyakar_id'] ?? 0);

    if ($mandal_name) {
        $cluster_id = $pdo->query("SELECT id FROM clusters LIMIT 1")->fetchColumn();
        if (!$cluster_id) {
            $pdo->exec("INSERT IGNORE INTO zones (zone_name) VALUES ('Default Zone')");
            $zid = $pdo->lastInsertId() ?: $pdo->query("SELECT id FROM zones LIMIT 1")->fetchColumn();
            $pdo->prepare("INSERT INTO clusters (cluster_name, zone_id) VALUES ('Default Cluster', ?)")->execute([$zid]);
            $cluster_id = $pdo->lastInsertId();
        }

        $pdo->prepare("INSERT INTO mandals (mandal_name, cluster_id, karyakar_id) VALUES (?,?,?)")
            ->execute([$mandal_name, $cluster_id, $karyakar_id ?: null]);
        $success = "Mandal (Mandir) added successfully!";
    } else {
        $error = "Mandal name is required.";
    }
}

// Handle edit mandal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $mandal_name = trim($_POST['mandal_name'] ?? '');
    $karyakar_id = (int)($_POST['karyakar_id'] ?? 0);

    if ($id && $mandal_name) {
        $pdo->prepare("UPDATE mandals SET mandal_name = ?, karyakar_id = ? WHERE id = ?")
            ->execute([$mandal_name, $karyakar_id ?: null, $id]);
        $success = "Mandal updated successfully!";
    } else {
        $error = "Mandal name is required.";
    }
}

// Handle delete mandal
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM mandals WHERE id = ?")->execute([$id]);
    $success = "Mandal deleted successfully!";
}

$mandals   = $pdo->query("SELECT m.*, u.full_name as karyakar_name FROM mandals m LEFT JOIN users u ON m.karyakar_id=u.id ORDER BY m.mandal_name ASC")->fetchAll();
$karyakars = $pdo->query("SELECT id, full_name FROM users WHERE role_id = (SELECT id FROM roles WHERE role_name='Karyakar' LIMIT 1)")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mandals — BAPS Bal Pravrutti</title>
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
                    <h2>🕌 Mandal Management</h2>
                    <p>Manage Mandirs and Bal Pravrutti units</p>
                </div>
                <button class="btn-primary-custom" onclick="openModal('addMandalModal')">
                    <i class="bi bi-plus-lg"></i> Add New Mandal
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
                    <h5><i class="bi bi-geo-alt me-2" style="color:var(--primary-light)"></i>Mandals / Mandirs</h5>
                </div>
                <div style="padding:0;">
                    <table class="table-dark-custom">
                        <thead>
                            <tr>
                                <th>Mandal Name</th>
                                <th>Karyakar</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mandals)): ?>
                            <tr><td colspan="4" style="text-align:center;padding:48px;color:var(--text-muted);">No mandals added yet.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($mandals as $m): ?>
                            <tr>
                                <td><span class="fw-600"><?php echo htmlspecialchars($m['mandal_name']); ?></span></td>
                                <td><span class="badge-custom badge-orange"><?php echo htmlspecialchars($m['karyakar_name'] ?? 'Unassigned'); ?></span></td>
                                <td style="text-align:right;">
                                    <button class="text-muted-custom me-2" style="background:none;border:none;" 
                                            onclick="openEditModal(<?php echo $m['id']; ?>, '<?php echo addslashes($m['mandal_name']); ?>', '<?php echo $m['karyakar_id']; ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="?delete=<?php echo $m['id']; ?>" class="text-danger-custom" onclick="return confirm('Are you sure you want to delete this mandal?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
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

<div class="modal-overlay" id="addMandalModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>Add New Mandal (Mandir)</h5>
            <button class="modal-close" onclick="closeModal('addMandalModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group-dark">
                    <label class="form-label-dark">Mandal Name</label>
                    <input type="text" name="mandal_name" class="form-control-dark" placeholder="e.g. Shahibaug Mandal 1" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Select Karyakar (Lead)</label>
                    <select name="karyakar_id" class="form-control-dark">
                        <option value="">Select Karyakar…</option>
                        <?php foreach ($karyakars as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('addMandalModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom">Create Mandal</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Mandal Modal -->
<div class="modal-overlay" id="editMandalModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5>Edit Mandal (Mandir)</h5>
            <button class="modal-close" onclick="closeModal('editMandalModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_mandal_id">
            <div class="modal-body">
                <div class="form-group-dark">
                    <label class="form-label-dark">Mandal Name</label>
                    <input type="text" name="mandal_name" id="edit_mandal_name" class="form-control-dark" placeholder="e.g. Shahibaug Mandal 1" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Select Karyakar (Lead)</label>
                    <select name="karyakar_id" id="edit_karyakar_id" class="form-control-dark">
                        <option value="">Select Karyakar…</option>
                        <?php foreach ($karyakars as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['full_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('editMandalModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom">Update Mandal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

function openEditModal(id, name, karyakarId) {
    document.getElementById('edit_mandal_id').value = id;
    document.getElementById('edit_mandal_name').value = name;
    document.getElementById('edit_karyakar_id').value = karyakarId || '';
    openModal('editMandalModal');
}
</script>
</body>
</html>
