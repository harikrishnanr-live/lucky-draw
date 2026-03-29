<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Check session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header('Location: login.php?error=Session expired. Please login again.');
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();

// Set current page for sidebar highlighting
$currentPage = 'dashboard';
$pageTitle = 'Admin Dashboard';

// Include header
require_once __DIR__ . '/includes/admin_header.php';

// Include sidebar
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="header">
    <h1>Admin Dashboard</h1>
</div>

<div class="container">
    <div class="welcome-section">
        <h2>Welcome to Lucky Draw Admin Panel</h2>
        <p>Manage your users and system settings from this dashboard.</p>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3>👥 User Management</h3>
            <p>View, create, and manage users with custom settings.</p>
            <a href="list_users.php">Manage Users</a>
        </div>

        <div class="dashboard-card">
            <h3>➕ Create User</h3>
            <p>Add a new user to the system with logo, title, and background.</p>
            <a href="create_user.php">Create New User</a>
        </div>
    </div>

    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="action-buttons">
            <a href="list_users.php" class="action-btn">Manage Users</a>
            <a href="create_user.php" class="action-btn">Create User</a>
        </div>
    </div>

    <div class="session-info">
        <strong>Session Information:</strong><br>
        Logged in as: <?php echo htmlspecialchars($_SESSION['admin_username']); ?><br>
        Login time: <?php echo date('Y-m-d H:i:s', $_SESSION['login_time']); ?>
    </div>
</div>

<style>
    .welcome-section {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .welcome-section h2 {
        color: #333;
        margin-bottom: 10px;
    }

    .welcome-section p {
        color: #666;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .dashboard-card {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    .dashboard-card h3 {
        color: #333;
        margin-bottom: 15px;
        font-size: 18px;
    }

    .dashboard-card p {
        color: #666;
        font-size: 14px;
        margin-bottom: 20px;
    }

    .dashboard-card a {
        display: inline-block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        transition: transform 0.2s ease;
    }

    .dashboard-card a:hover {
        transform: translateY(-2px);
    }

    .quick-actions {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .quick-actions h3 {
        color: #333;
        margin-bottom: 20px;
    }

    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
    }

    .action-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .session-info {
        margin-top: 30px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 5px;
        font-size: 12px;
        color: #666;
    }
</style>

<?php
// Include footer
require_once __DIR__ . '/includes/admin_footer.php';
?>
