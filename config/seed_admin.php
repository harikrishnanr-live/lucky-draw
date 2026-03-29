<?php
/**
 * Admin User Seeder
 * 
 * This script creates the admin user with a hashed password.
 * Run this after running migrations.
 * 
 * Usage:
 *   php config/seed_admin.php
 * 
 * Or access via browser:
 *   http://your-domain/config/seed_admin.php
 */

// Display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin User Seeder</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: #17a2b8; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; padding: 10px; background: #fff3cd; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #5a6fd6; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🎰 Lucky Draw - Admin User Seeder</h1>";

// Load database configuration
require_once __DIR__ . '/database.php';

// Assign constants to variables for bind_param()
$dbUsername = ADMIN_USERNAME;
$dbPassword = ADMIN_PASSWORD;

echo "<div class='info'>
        <strong>Database Configuration:</strong><br>
        Host: " . DB_HOST . "<br>
        Database: " . DB_NAME . "<br>
        User: " . DB_USER . "
      </div>";

// Connect to database
echo "<h2>Step 1: Connecting to Database...</h2>";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo "<div class='error'>❌ Connection failed: " . $conn->connect_error . "</div>";
    echo "<p>Please run migrations first: <code>php config/run_migrations.php</code></p>";
    exit();
}

echo "<div class='success'>✅ Connected to database successfully</div>";

// Set charset
$conn->set_charset("utf8mb4");

// Check if admin_users table exists
echo "<h2>Step 2: Checking admin_users table...</h2>";
$result = $conn->query("SHOW TABLES LIKE 'admin_users'");

if ($result->num_rows === 0) {
    echo "<div class='error'>❌ admin_users table does not exist</div>";
    echo "<p>Please run migrations first: <code>php config/run_migrations.php</code></p>";
    exit();
}

echo "<div class='success'>✅ admin_users table exists</div>";

// Check if admin user already exists
echo "<h2>Step 3: Checking for existing admin user...</h2>";
$stmt = $conn->prepare("SELECT id, username FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $dbUsername);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<div class='warning'>⚠️ Admin user already exists (ID: " . $admin['id'] . ")</div>";
    echo "<p>Updating password with new hash...</p>";
    
    // Update password
    $hashedPassword = password_hash($dbPassword, PASSWORD_DEFAULT);
    $updateStmt = $conn->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE username = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $dbUsername);
    
    if ($updateStmt->execute()) {
        echo "<div class='success'>✅ Admin password updated successfully</div>";
        echo "<div class='info'>";
        echo "<strong>Password Details:</strong><br>";
        echo "Plain text: " . $dbPassword . "<br>";
        echo "Hashed: " . $hashedPassword . "<br>";
        echo "Algorithm: PASSWORD_DEFAULT (bcrypt)";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Failed to update password: " . $updateStmt->error . "</div>";
    }
    
    $updateStmt->close();
} else {
    // Create admin user with hashed password
    echo "<h2>Step 4: Creating admin user...</h2>";
    
    $hashedPassword = password_hash($dbPassword, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, email, logo, common_title, year, background_image) VALUES (?, ?, ?, NULL, NULL, NULL, NULL)");
    $stmt->bind_param("sss", $dbUsername, $hashedPassword, $dbUsername);
    
    if ($stmt->execute()) {
        echo "<div class='success'>✅ Admin user created successfully</div>";
        echo "<div class='info'>";
        echo "<strong>User Details:</strong><br>";
        echo "ID: " . $stmt->insert_id . "<br>";
        echo "Username: " . $dbUsername . "<br>";
        echo "Email: " . $dbUsername . "<br>";
        echo "Password (plain): " . $dbPassword . "<br>";
        echo "Password (hashed): " . $hashedPassword . "<br>";
        echo "Algorithm: PASSWORD_DEFAULT (bcrypt)";
        echo "</div>";
    } else {
        echo "<div class='error'>❌ Failed to create admin user: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}

// Verify the user was created/updated
echo "<h2>Step 5: Verifying admin user...</h2>";
$result = $conn->query("SELECT id, username, email, created_at, updated_at FROM admin_users WHERE username = '" . $dbUsername . "'");

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    echo "<div class='success'>✅ Admin user verified</div>";
    echo "<div class='info'>";
    echo "<strong>Database Record:</strong><br>";
    echo "ID: " . $admin['id'] . "<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Created: " . $admin['created_at'] . "<br>";
    echo "Updated: " . $admin['updated_at'];
    echo "</div>";
} else {
    echo "<div class='error'>❌ Admin user not found in database</div>";
}

// Close connection
$conn->close();

echo "<div class='success' style='margin-top: 30px;'>
        <strong>🎉 Setup Complete!</strong><br><br>
        You can now login to the admin panel with:<br>
        <strong>Username:</strong> " . $dbUsername . "<br>
        <strong>Password:</strong> " . $dbPassword . "<br><br>
        <em>Note: Password is stored as a secure bcrypt hash in the database.</em>
      </div>";

echo "<a href='../admin/login.php' class='btn'>Go to Admin Login</a>";

echo "</div></body></html>";
?>
