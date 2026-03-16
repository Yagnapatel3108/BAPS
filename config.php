<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bal_pravrutti');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Global settings
session_start();
date_default_timezone_set('Asia/Kolkata');

// Helper function to check login status and roles
function checkRole($allowed_roles, $redirect = true) {
    if (!isset($_SESSION['user_id'])) {
        if ($redirect) {
            header("Location: login.php");
            exit();
        }
        return false;
    }
    
    if (!in_array($_SESSION['role_name'], $allowed_roles)) {
        if ($redirect) {
            header("Location: 403.php");
            exit();
        }
        return false;
    }
    return true;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
