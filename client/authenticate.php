<?php
/**
 * Client Authentication Handler
 * Authenticates client user and redirects to dashboard
 */

// Check if database connection already exists
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../includes/db_connect.php';
}

session_start();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /client/login.php');
    exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validate input
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Username and password are required';
    header('Location: /' . $username);
    exit;
}

// Prepare statement to prevent SQL injection
$stmt = $conn->prepare("SELECT id, username, password, logo, common_title, year, background_image, status FROM users WHERE username = ? AND status = 'active'");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Invalid username or password';
    header('Location: /' . $username);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Verify password (assuming password is stored as plain text or hashed)
// For now, we'll use plain text comparison. In production, use password_verify()
if ($password !== $user['password']) {
    $_SESSION['error'] = 'Invalid username or password';
    header('Location: /' . $username);
    exit;
}

// Authentication successful - set session variables
$_SESSION['client_user_id'] = $user['id'];
$_SESSION['client_username'] = $user['username'];
$_SESSION['client_logo'] = $user['logo'];
$_SESSION['client_common_title'] = $user['common_title'];
$_SESSION['client_year'] = $user['year'];
$_SESSION['client_background_image'] = $user['background_image'];
$_SESSION['client_logged_in'] = true;
$_SESSION['client_login_time'] = date('Y-m-d H:i:s');

// Set localStorage values via JavaScript redirect
$redirect_url = '/client/dashboard.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authenticating...</title>
    <script>
        // Store client session in localStorage
        localStorage.setItem('clientUser', '<?php echo htmlspecialchars($user['username']); ?>');
        localStorage.setItem('clientLoginTime', new Date().toISOString());
        localStorage.setItem('clientExpirationTime', new Date(Date.now() + 60 * 60 * 1000).toISOString());
        
        // Redirect to dashboard
        window.location.href = '<?php echo $redirect_url; ?>';
    </script>
</head>
<body>
    <p>Redirecting to dashboard...</p>
</body>
</html>
