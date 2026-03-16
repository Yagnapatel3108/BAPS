<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden — BAPS Bal Pravrutti</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: var(--dark-bg); color: var(--text-primary); display: flex; align-items: center; justify-content: center; height: 100vh; text-align: center; padding: 20px; }
        .err-icon { font-size: 80px; color: var(--danger); margin-bottom: 24px; animation: shake 0.5s ease-in-out; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-10px); } 75% { transform: translateX(10px); } }
        h1 { font-size: 120px; font-weight: 800; line-height: 1; margin: 0; opacity: 0.1; position: absolute; }
    </style>
</head>
<body>
    <h1>403</h1>
    <div style="position:relative; z-index:1;">
        <div class="err-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <h2 style="font-weight:700;">Access Denied</h2>
        <p style="color:var(--text-muted); max-width:400px; margin: 10px auto 30px;">
            You do not have permission to access this page. This action has been logged for security verification.
        </p>
        <a href="dashboard.php" class="btn-primary-custom" style="padding:12px 30px;">Back to Dashboard</a>
    </div>
</body>
</html>
