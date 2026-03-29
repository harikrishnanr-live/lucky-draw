<?php
/**
 * Client Logout Handler
 * Destroys session and redirects to login page
 */

session_start();

// Get username before destroying session
$username = isset($_SESSION['client_username']) ? $_SESSION['client_username'] : '';

// Destroy session
session_unset();
session_destroy();

// Redirect to login page
if (!empty($username)) {
    header("Location: /" . $username);
} else {
    header("Location: /");
}
exit;
?>
