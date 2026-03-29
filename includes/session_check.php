<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit();
}

// Check session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header('Location: /admin/login.php?error=Session expired. Please login again.');
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();
?>
