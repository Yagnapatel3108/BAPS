<?php
require_once 'config.php';
checkRole(['Saint']);

$success = $error = '';

// Handle add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role_id   = (int)($_POST['role_id'] ?? 0);

    if ($full_name && $email && $password && $role_id) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?,?,?,?)")
                ->execute([$full_name, $email, $hash, $role_id]);
            $success = "User added successfully!";
        }
    } else {
        $error = "All fields are required.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== (int)$_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        $success = "User deleted successfully!";
    } else {
        $error = "You cannot delete yourself.";
    }
}

$users = $pdo->query("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id ORDER BY u.created_at DESC")->fetchAll();
$roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — BAPS Bal Pravrutti</title>
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
                    <h2>👥 User Management</h2>
                    <p>Manage administrative roles and access control</p>
                </div>
                <button class="btn-primary-custom" onclick="openModal('addUserModal')">
                    <i class="bi bi-person-plus"></i> Add New User
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
                    <h5><i class="bi bi-people me-2" style="color:var(--primary-light)"></i>Registered Users</h5>
                    <div style="position:relative;">
                        <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);font-size:14px;"></i>
                        <input type="text" id="userInput" class="form-control-dark" placeholder="Search users…" style="padding-left:36px;width:220px;" oninput="filterUsers()">
                    </div>
                </div>
                <div style="padding:0;">
                    <table class="table-dark-custom" id="usersTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px;">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['full_name']); ?>&background=random&color=fff&bold=true" style="width:34px;height:34px;border-radius:50%;">
                                        <span class="fw-600"><?php echo htmlspecialchars($u['full_name']); ?></span>
                                    </div>
                                </td>
                                <td><span class="text-muted-custom"><?php echo htmlspecialchars($u['email']); ?></span></td>
                                <td><span class="badge-custom badge-blue"><?php echo htmlspecialchars($u['role_name']); ?></span></td>
                                <td><span class="badge-custom badge-green">Active</span></td>
                                <td style="text-align:right;">
                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $u['id']; ?>" class="text-danger-custom" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                    <?php endif; ?>
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

<div class="modal-overlay" id="addUserModal">
    <div class="modal-box">
        <div class="modal-header">
            <h5><i class="bi bi-person-plus me-2" style="color:var(--primary-light)"></i>Add New User</h5>
            <button class="modal-close" onclick="closeModal('addUserModal')"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <div class="modal-body">
                <div class="form-group-dark">
                    <label class="form-label-dark">Full Name</label>
                    <input type="text" name="full_name" class="form-control-dark" placeholder="Enter full name" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Email Address</label>
                    <input type="email" name="email" class="form-control-dark" placeholder="user@example.com" required>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Role</label>
                    <select name="role_id" class="form-control-dark" required>
                        <option value="">Select Role…</option>
                        <?php foreach ($roles as $r): ?>
                        <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['role_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-dark">
                    <label class="form-label-dark">Password</label>
                    <input type="password" name="password" class="form-control-dark" placeholder="Enter password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary-custom" onclick="closeModal('addUserModal')">Cancel</button>
                <button type="submit" class="btn-primary-custom">Create User</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }
function filterUsers() {
    const q = document.getElementById('userInput').value.toLowerCase();
    document.querySelectorAll('#usersTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
</script>
</body>
</html>
