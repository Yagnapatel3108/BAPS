<?php
// setup_database.php — Run this ONCE to create all tables
// Access via: http://localhost/BAL_PRAVRUTTI/setup_database.php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bal_pravrutti');

echo "<!DOCTYPE html><html><head><title>Database Setup</title>";
echo "<link rel='preconnect' href='https://fonts.googleapis.com'>";
echo "<link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap' rel='stylesheet'>";
echo "<style>
body { font-family: Inter, sans-serif; background: #0f1117; color: #eaeef5; padding: 40px; max-width: 700px; margin: 0 auto; }
h1 { color: #f36f21; margin-bottom: 24px; }
.step { background: #1a1f2e; border: 1px solid #252a3a; border-radius: 10px; padding: 16px 20px; margin-bottom: 14px; display: flex; align-items: center; gap: 14px; }
.ok  { color: #22c55e; font-size: 22px; }
.err { color: #ef4444; font-size: 22px; }
.msg { font-size: 14px; }
.label { font-weight: 700; font-size: 15px; }
.btn { display: inline-block; margin-top: 24px; padding: 12px 28px; background: linear-gradient(135deg,#f36f21,#ff8c42); border-radius: 8px; color: white; text-decoration: none; font-weight: 700; font-size: 15px; }
</style></head><body>";
echo "<h1>🛠️ BAPS BPMS — Database Setup</h1>";

$steps = [];
$all_ok = true;

// Step 1: Connect without DB
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $steps[] = ['ok', 'MySQL Connection', 'Connected to MySQL successfully'];
} catch (PDOException $e) {
    $steps[] = ['err', 'MySQL Connection', 'Failed: ' . $e->getMessage()];
    $all_ok = false;
    foreach ($steps as $s) {
        echo "<div class='step'><span class='" . $s[0] . "'>" . ($s[0]==='ok'?'✓':'✗') . "</span><div><div class='label'>{$s[1]}</div><div class='msg'>{$s[2]}</div></div></div>";
    }
    echo "</body></html>"; exit;
}

// Step 2: Create DB
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $steps[] = ['ok', 'Database Created', 'Database `' . DB_NAME . '` is ready'];
} catch (PDOException $e) {
    $steps[] = ['err', 'Database Creation', 'Failed: ' . $e->getMessage()];
    $all_ok = false;
}

$pdo->exec("USE `" . DB_NAME . "`");

// Step 3: Create tables
$tables = [
    'Roles' => "CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role_name VARCHAR(50) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    'Zones' => "CREATE TABLE IF NOT EXISTS zones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        zone_name VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB",

    'Clusters' => "CREATE TABLE IF NOT EXISTS clusters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cluster_name VARCHAR(100) NOT NULL,
        zone_id INT,
        FOREIGN KEY (zone_id) REFERENCES zones(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    'Mandals' => "CREATE TABLE IF NOT EXISTS mandals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mandal_name VARCHAR(100) NOT NULL,
        cluster_id INT,
        karyakar_id INT,
        FOREIGN KEY (cluster_id) REFERENCES clusters(id) ON DELETE CASCADE,
        FOREIGN KEY (karyakar_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB",

    'Users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role_id INT,
        zone_id INT DEFAULT NULL,
        cluster_id INT DEFAULT NULL,
        mandal_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id),
        FOREIGN KEY (zone_id) REFERENCES zones(id),
        FOREIGN KEY (cluster_id) REFERENCES clusters(id),
        FOREIGN KEY (mandal_id) REFERENCES mandals(id)
    ) ENGINE=InnoDB",

    'Balaks' => "CREATE TABLE IF NOT EXISTS balaks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        mandal_id INT,
        dob DATE,
        contact_number VARCHAR(15),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mandal_id) REFERENCES mandals(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    'Attendance' => "CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        balak_id INT,
        status ENUM('Present','Absent') NOT NULL,
        attendance_date DATE NOT NULL,
        marked_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (balak_id) REFERENCES balaks(id) ON DELETE CASCADE,
        FOREIGN KEY (marked_by) REFERENCES users(id)
    ) ENGINE=InnoDB",

    'Mukhpath Progress' => "CREATE TABLE IF NOT EXISTS mukhpath_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        balak_id INT,
        item_name VARCHAR(255) NOT NULL,
        status ENUM('Pending','In Progress','Completed') DEFAULT 'Pending',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (balak_id) REFERENCES balaks(id) ON DELETE CASCADE
    ) ENGINE=InnoDB",

    'Sampark' => "CREATE TABLE IF NOT EXISTS sampark (
        id INT AUTO_INCREMENT PRIMARY KEY,
        balak_id INT,
        visit_date DATE NOT NULL,
        remarks TEXT,
        visited_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (balak_id) REFERENCES balaks(id) ON DELETE CASCADE,
        FOREIGN KEY (visited_by) REFERENCES users(id)
    ) ENGINE=InnoDB",

    'Announcements' => "CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_by INT,
        target_role_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (target_role_id) REFERENCES roles(id)
    ) ENGINE=InnoDB",
];

foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        $steps[] = ['ok', "Table: $name", "Created / already exists"];
    } catch (PDOException $e) {
        $steps[] = ['err', "Table: $name", 'Failed: ' . $e->getMessage()];
        $all_ok = false;
    }
}

// Step 4: Seed roles
try {
    $count = $pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO roles (role_name) VALUES ('Saint'),('Nirdheshak'),('Agresar'),('Nirikshak'),('Karyakar'),('Sah-Karyakar')");
        $steps[] = ['ok', 'Seed Roles', 'Inserted 6 default roles'];
    } else {
        $steps[] = ['ok', 'Seed Roles', "Roles already exist ($count roles found)"];
    }
} catch (PDOException $e) {
    $steps[] = ['err', 'Seed Roles', 'Failed: ' . $e->getMessage()];
    $all_ok = false;
}

// Step 5: Seed default admin user
try {
    $exists = $pdo->query("SELECT COUNT(*) FROM users WHERE email='saint@baps.org'")->fetchColumn();
    if ($exists == 0) {
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (full_name, email, password, role_id) VALUES (?,?,?,1)")
            ->execute(['Main Saint', 'saint@baps.org', $hash]);
        $steps[] = ['ok', 'Default Admin', 'Created → Email: saint@baps.org | Password: admin123'];
    } else {
        // Update password hash to ensure it's correct
        $hash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=? WHERE email='saint@baps.org'")->execute([$hash]);
        $steps[] = ['ok', 'Default Admin', 'Already exists — password reset to: admin123'];
    }
} catch (PDOException $e) {
    $steps[] = ['err', 'Default Admin', 'Failed: ' . $e->getMessage()];
    $all_ok = false;
}

// Print all steps
foreach ($steps as $s) {
    $icon = $s[0] === 'ok' ? '✓' : '✗';
    echo "<div class='step'><span class='{$s[0]}'>{$icon}</span><div><div class='label'>{$s[1]}</div><div class='msg'>{$s[2]}</div></div></div>";
}

if ($all_ok) {
    echo "<div style='background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);border-radius:10px;padding:20px 24px;margin-top:20px;'>";
    echo "<h3 style='color:#22c55e;margin:0 0 10px;'>🎉 Database Setup Complete!</h3>";
    echo "<p style='color:#86efac;margin:0 0 6px;'>You can now login with:</p>";
    echo "<p style='margin:0 0 4px;'><strong>Email:</strong> saint@baps.org</p>";
    echo "<p style='margin:0;'><strong>Password:</strong> admin123</p>";
    echo "</div>";
    echo "<a href='login.php' class='btn'>→ Go to Login</a>";
} else {
    echo "<div style='background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:20px 24px;margin-top:20px;color:#fca5a5;'>";
    echo "<strong>⚠️ Some steps failed.</strong> Please make sure WampServer/MySQL is running and try again.";
    echo "</div>";
}

echo "</body></html>";
?>
