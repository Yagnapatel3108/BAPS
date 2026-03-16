<?php
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['full_name']  = $user['full_name'];
            $_SESSION['role_id']    = $user['role_id'];
            $_SESSION['role_name']  = $user['role_name'];
            $_SESSION['zone_id']    = $user['zone_id'];
            $_SESSION['cluster_id'] = $user['cluster_id'];
            $_SESSION['mandal_id']  = $user['mandal_id'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password. Please try again.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — BAPS Bal Pravrutti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-wrapper">

    <div class="auth-box">

        <!-- Logo -->
        <div class="auth-logo">
            <div class="auth-logo-icon">🙏</div>
            <h1>BAPS Bal Pravrutti</h1>
            <p>Management System — Admin Portal</p>
        </div>

        <!-- Card -->
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account to continue</p>

            <?php if ($error): ?>
            <div class="alert-danger-custom">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            required
                            autocomplete="email"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
                        >
                        <button type="button" class="toggle-pw" id="togglePw" aria-label="Toggle password">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span id="loginText">Sign In</span>
                </button>
            </form>

            <p style="text-align:center; margin-top:20px; font-size:12px; color:var(--text-dim);">
                BAPS Bal Pravrutti Management System &copy; <?php echo date('Y'); ?>
            </p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePw').addEventListener('click', function () {
            const pwInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pwInput.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });

        // Loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('loginBtn');
            document.getElementById('loginText').textContent = 'Signing in…';
            btn.disabled = true;
            btn.style.opacity = '0.8';
        });
    </script>
</body>
</html>
