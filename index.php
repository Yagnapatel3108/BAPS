<?php
// index.php — Initial redirect
require_once 'config.php';

if (isLoggedIn()) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
