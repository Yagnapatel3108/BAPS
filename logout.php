<?php
// logout.php — Clear session and show stylish logout page
require_once 'config.php';

$name = $_SESSION['full_name'] ?? 'User';

session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logged Out — BAPS Bal Pravrutti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .logout-icon {
            width: 80px;
            height: 80px;
            background: rgba(34,197,94,0.1);
            border: 2px solid rgba(34,197,94,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 36px;
            color: var(--success);
            animation: scaleIn 0.5s cubic-bezier(0.4,0,0.2,1);
        }
        @keyframes scaleIn {
            from { transform: scale(0.5); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        .countdown {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 12px;
        }
    </style>
</head>
<body class="logout-wrapper">
    <div class="logout-card">
        <div class="logout-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2 style="font-size:22px;font-weight:800;margin-bottom:8px;">Logged Out</h2>
        <p style="color:var(--text-muted);font-size:14px;margin-bottom:28px;">
            You have been successfully signed out of BAPS Bal Pravrutti Management System. Stay blessed! 🙏
        </p>
        <a href="login.php" class="btn-login" style="display:block;text-align:center;text-decoration:none;">
            <i class="bi bi-arrow-left me-2"></i> Back to Login
        </a>
        <p class="countdown" id="countdown">Redirecting in <strong id="timer">5</strong> seconds…</p>
    </div>

    <script>
        let t = 5;
        const el = document.getElementById('timer');
        const interval = setInterval(function() {
            t--;
            if (el) el.textContent = t;
            if (t <= 0) {
                clearInterval(interval);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
</body>
</html>
