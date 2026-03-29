<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validate input
    if (empty($username) || empty($password)) {
        header('Location: login.php?error=Please enter both username and password');
        exit();
    }

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // Verify password using password_verify (supports bcrypt)
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['login_time'] = time();

            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            // Invalid password
            header('Location: login.php?error=Invalid username or password');
            exit();
        }
    } else {
        // User not found
        header('Location: login.php?error=Invalid username or password');
        exit();
    }

    $stmt->close();
} else {
    // Direct access not allowed
    header('Location: login.php');
    exit();
}

$conn->close();
?>
