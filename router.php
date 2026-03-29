<?php
/**
 * PHP Built-in Server Router
 * 
 * This file handles routing for the PHP built-in server.
 * Usage: php -S localhost:8000 router.php
 */

// Get the requested URI
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Remove query string
$path = strtok($path, '?');

// Route mapping
$routes = [
    '/admin' => 'admin/login.php',
    '/admin/' => 'admin/login.php',
    '/admin/login' => 'admin/login.php',
    '/admin/login/' => 'admin/login.php',
    '/admin/login.php' => 'admin/login.php',
    '/admin/dashboard' => 'admin/dashboard.php',
    '/admin/dashboard/' => 'admin/dashboard.php',
    '/admin/dashboard.php' => 'admin/dashboard.php',
    '/admin/logout' => 'admin/logout.php',
    '/admin/logout/' => 'admin/logout.php',
    '/admin/logout.php' => 'admin/logout.php',
    '/admin/authenticate' => 'admin/authenticate.php',
    '/admin/authenticate/' => 'admin/authenticate.php',
    '/admin/authenticate.php' => 'admin/authenticate.php',
    '/client/login' => 'client/login.php',
    '/client/login/' => 'client/login.php',
    '/client/login.php' => 'client/login.php',
    '/client/authenticate' => 'client/authenticate.php',
    '/client/authenticate/' => 'client/authenticate.php',
    '/client/authenticate.php' => 'client/authenticate.php',
    '/client/dashboard' => 'client/dashboard.php',
    '/client/dashboard/' => 'client/dashboard.php',
    '/client/dashboard.php' => 'client/dashboard.php',
    '/client/upload-participants' => 'client/upload-participants.php',
    '/client/upload-participants/' => 'client/upload-participants.php',
    '/client/upload-participants.php' => 'client/upload-participants.php',
    '/client/lucky-draw' => 'client/lucky-draw.php',
    '/client/lucky-draw/' => 'client/lucky-draw.php',
    '/client/lucky-draw.php' => 'client/lucky-draw.php',
    '/client/winners' => 'client/winners.php',
    '/client/winners/' => 'client/winners.php',
    '/client/winners.php' => 'client/winners.php',
    '/client/logout' => 'client/logout.php',
    '/client/logout/' => 'client/logout.php',
    '/client/logout.php' => 'client/logout.php',
];

// Check if the route exists in static routes
if (isset($routes[$path])) {
    $file = $routes[$path];
    
    // Check if file exists
    if (file_exists($file)) {
        // Include the file
        include $file;
        return true;
    }
}

// Check if the file exists directly - serve it as-is
// This handles direct file access like /client/login.php, /styles.css, etc.
if (file_exists(__DIR__ . $path)) {
    // Serve static files (CSS, JS, images, etc.)
    $extension = pathinfo($path, PATHINFO_EXTENSION);
    
    // Set appropriate content type
    $contentTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'html' => 'text/html',
        'htm' => 'text/html',
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
        'php' => 'text/html', // PHP files will be processed by the server
    ];
    
    if (isset($contentTypes[$extension])) {
        header('Content-Type: ' . $contentTypes[$extension]);
    }
    
    // For PHP files, let the built-in server process them
    if ($extension === 'php') {
        return false; // Let the built-in server handle PHP files
    }
    
    // For other files, serve them directly
    readfile(__DIR__ . $path);
    return true;
}

// Dynamic client routing: Check if path is a username from users table
// This handles routes like /hari, /bis, etc.
// Only process if path doesn't contain '/' (single segment) and doesn't start with /client/ or /admin/
$trimmed_path = trim($path, '/');
if (!empty($trimmed_path) && !str_contains($trimmed_path, '/') && !str_starts_with($trimmed_path, 'client/') && !str_starts_with($trimmed_path, 'admin/')) {
    // Check if this is a client username
    require_once __DIR__ . '/includes/db_connect.php';
    
    $username = $trimmed_path;
    $stmt = $conn->prepare("SELECT id, username, status FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // User found - serve client login page
        $stmt->close();
        // Don't close connection here - login.php needs it
        include __DIR__ . '/client/login.php';
        return true;
    }
    
    $stmt->close();
    $conn->close();
}

// 404 Not Found
http_response_code(404);
echo "404 Not Found: The requested resource $path was not found on this server.";
return true;
?>
