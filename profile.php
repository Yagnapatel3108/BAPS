<?php
require_once 'config.php';
if (!isLoggedIn()) { header("Location: login.php"); exit(); }

$success = $error = '';
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    if ($full_name) {
        $pdo->prepare("UPDATE users SET full_name = ? WHERE id = ?")->execute([$full_name, $user_id]);
        $_SESSION['full_name'] = $full_name;
        $success = "Profile updated successfully!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $current_pw = $_POST['current_pw'] ?? '';
    $new_pw     = $_POST['new_pw'] ?? '';
    $confirm_pw = $_POST['confirm_pw'] ?? '';

    $user = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $user->execute([$user_id]);
    $hash = $user->fetchColumn();

    if (password_verify($current_pw, $hash)) {
        if ($new_pw === $confirm_pw) {
            $new_hash = password_hash($new_pw, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$new_hash, $user_id]);
            $success = "Password changed successfully!";
        } else {
            $error = "New passwords do not match.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}

$user = $pdo->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
$user->execute([$user_id]);
$u = $user->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — BAPS Bal Pravrutti</title>
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

            <div class="page-header">
                <h2>👤 My Profile</h2>
                <p>Manage your account settings and preferences</p>
            </div>

            <?php if ($success): ?>
            <div class="alert-custom alert-success"><i class="bi bi-check-circle"></i><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="alert-custom alert-error"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="grid-2">
                <!-- Profile Info -->
                <div class="card">
                    <div class="card-header"><h5>Personal Information</h5></div>
                    <div class="card-body">
                        <div style="text-align:center;margin-bottom:24px;">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($u['full_name']); ?>&background=f36f21&color=fff&size=120&bold=true" style="border-radius:50%;border:4px solid var(--card-border);">
                            <h4 style="margin-top:16px;"><?php echo htmlspecialchars($u['full_name']); ?></h4>
                            <span class="badge-custom badge-orange"><?php echo htmlspecialchars($u['role_name']); ?></span>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group-dark">
                                <label class="form-label-dark">Full Name</label>
                                <input type="text" name="full_name" class="form-control-dark" value="<?php echo htmlspecialchars($u['full_name']); ?>" required>
                            </div>
                            <div class="form-group-dark">
                                <label class="form-label-dark">Email (Cannot be changed)</label>
                                <input type="email" class="form-control-dark" value="<?php echo htmlspecialchars($u['email']); ?>" disabled style="opacity:0.6;">
                            </div>
                            <button type="submit" class="btn-primary-custom w-100">Update Profile</button>
                        </form>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="card">
                    <div class="card-header"><h5>Security</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-group-dark">
                                <label class="form-label-dark">Current Password</label>
                                <input type="password" name="current_pw" class="form-control-dark" required>
                            </div>
                            <div class="form-group-dark">
                                <label class="form-label-dark">New Password</label>
                                <input type="password" name="new_pw" class="form-control-dark" required>
                            </div>
                            <div class="form-group-dark">
                                <label class="form-label-dark">Confirm New Password</label>
                                <input type="password" name="confirm_pw" class="form-control-dark" required>
                            </div>
                            <button type="submit" class="btn-secondary-custom w-100">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</body>
</html>
