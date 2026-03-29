<?php
/**
 * Database Migration Runner
 * 
 * This script runs all migration files in order to set up the database.
 * 
 * Usage:
 *   php config/run_migrations.php
 * 
 * Or access via browser:
 *   http://your-domain/config/run_migrations.php
 */

// Display errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Migration Runner</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #28a745; padding: 10px; background: #d4edda; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; padding: 10px; background: #f8d7da; border-radius: 5px; margin: 10px 0; }
        .info { color: #17a2b8; padding: 10px; background: #d1ecf1; border-radius: 5px; margin: 10px 0; }
        .migration { padding: 10px; margin: 5px 0; background: #f8f9fa; border-left: 4px solid #667eea; }
        .btn { display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
        .btn:hover { background: #5a6fd6; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🎰 Lucky Draw - Database Migration Runner</h1>";

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

// Connect to MySQL
echo "<h2>Step 1: Connecting to MySQL...</h2>";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    echo "<div class='error'>❌ Connection failed: " . $conn->connect_error . "</div>";
    echo "<p>Please check your database configuration in <code>config/database.php</code></p>";
    exit();
}

echo "<div class='success'>✅ Connected to MySQL successfully</div>";

// Create database if not exists
echo "<h2>Step 2: Creating Database...</h2>";
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    echo "<div class='success'>✅ Database '" . DB_NAME . "' created or already exists</div>";
} else {
    echo "<div class='error'>❌ Error creating database: " . $conn->error . "</div>";
    exit();
}

// Select database
$conn->select_db(DB_NAME);
echo "<div class='success'>✅ Database '" . DB_NAME . "' selected</div>";

// Set charset
$conn->set_charset("utf8mb4");
echo "<div class='success'>✅ Character set set to utf8mb4</div>";

// Get migration files
echo "<h2>Step 3: Running Migrations...</h2>";
$migrationDir = __DIR__ . '/migrations/';
$migrationFiles = glob($migrationDir . '*.sql');
sort($migrationFiles);

if (empty($migrationFiles)) {
    echo "<div class='error'>❌ No migration files found in " . $migrationDir . "</div>";
    exit();
}

echo "<div class='info'>Found " . count($migrationFiles) . " migration file(s)</div>";

$successCount = 0;
$errorCount = 0;

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    echo "<div class='migration'>";
    echo "<strong>Running: " . $filename . "</strong><br>";
    
    // Read SQL file
    $sql = file_get_contents($file);
    
    // Remove comments and split by semicolon
    $sql = preg_replace('/--.*$/m', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    $fileSuccess = true;
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        if (!$conn->query($statement)) {
            echo "<div class='error'>❌ Error: " . $conn->error . "</div>";
            $fileSuccess = false;
            break;
        }
    }
    
    if ($fileSuccess) {
        echo "<div class='success'>✅ Migration completed successfully</div>";
        $successCount++;
    } else {
        $errorCount++;
    }
    
    echo "</div>";
}

// Summary
echo "<h2>Migration Summary</h2>";
echo "<div class='info'>";
echo "<strong>Total migrations:</strong> " . count($migrationFiles) . "<br>";
echo "<strong>Successful:</strong> " . $successCount . "<br>";
echo "<strong>Failed:</strong> " . $errorCount . "<br>";
echo "</div>";

if ($errorCount === 0) {
    echo "<div class='success'>🎉 All migrations completed successfully!</div>";
    
    // Run admin seeder
    echo "<h2>Step 4: Seeding Admin User...</h2>";
    echo "<div class='info'>Running admin seeder with hashed password...</div>";
    
    // Include and run the seeder logic
    $hashedPassword = password_hash($dbPassword, PASSWORD_DEFAULT);
    
    // Check if admin user exists
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $stmt->bind_param("s", $dbUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing admin
        $updateStmt = $conn->prepare("UPDATE admin_users SET password = ?, updated_at = NOW() WHERE username = ?");
        $updateStmt->bind_param("ss", $hashedPassword, $dbUsername);
        
        if ($updateStmt->execute()) {
            echo "<div class='success'>✅ Admin password updated with secure hash</div>";
        } else {
            echo "<div class='error'>❌ Failed to update admin password</div>";
        }
        $updateStmt->close();
    } else {
        // Create new admin
        $insertStmt = $conn->prepare("INSERT INTO admin_users (username, password, email) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $dbUsername, $hashedPassword, $dbUsername);
        
        if ($insertStmt->execute()) {
            echo "<div class='success'>✅ Admin user created with secure hash</div>";
        } else {
            echo "<div class='error'>❌ Failed to create admin user</div>";
        }
        $insertStmt->close();
    }
    $stmt->close();
    
    echo "<div class='info'>";
    echo "<strong>Admin Credentials:</strong><br>";
    echo "Username: " . $dbUsername . "<br>";
    echo "Password: " . $dbPassword . "<br>";
    echo "<em>(Password stored as bcrypt hash in database)</em>";
    echo "</div>";
    
    echo "<p>You can now access the admin panel:</p>";
    echo "<a href='../admin/login.php' class='btn'>Go to Admin Login</a>";
} else {
    echo "<div class='error'>⚠️ Some migrations failed. Please check the errors above.</div>";
}

// Close connection
$conn->close();

echo "</div></body></html>";
?>
