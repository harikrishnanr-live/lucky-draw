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

// Load database configuration
require_once __DIR__ . '/../config/database.php';

$message = '';
$messageType = '';

// Handle delete action
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        $message = 'Database connection failed: ' . $conn->connect_error;
        $messageType = 'error';
    } else {
        $conn->set_charset("utf8mb4");
        
        // Delete user from users table
        $deleteStmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $deleteStmt->bind_param("i", $userId);
        
        if ($deleteStmt->execute()) {
            $message = 'User deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete user: ' . $deleteStmt->error;
            $messageType = 'error';
        }
        $deleteStmt->close();
        $conn->close();
    }
}

// Fetch all users
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$users = [];

if ($conn->connect_error) {
    $message = 'Database connection failed: ' . $conn->connect_error;
    $messageType = 'error';
} else {
    $conn->set_charset("utf8mb4");
    $result = $conn->query("SELECT id, user_code, username, email, full_name, phone, company, logo, common_title, year, background_image, status, created_at FROM users ORDER BY created_at DESC");
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    $conn->close();
}

// Set current page for sidebar highlighting
$currentPage = 'list_users';
$pageTitle = 'User List';

// Include header
require_once __DIR__ . '/includes/admin_header.php';

// Include sidebar
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="header">
    <h1>User Management</h1>
</div>

<div class="container">
    <div class="page-header">
        <h2>All Users</h2>
        <a href="create_user.php" class="create-btn">+ Create New User</a>
    </div>

    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="stats-cards">
        <div class="stat-card">
            <h3>Total Users</h3>
            <div class="value"><?php echo count($users); ?></div>
        </div>
        <div class="stat-card">
            <h3>With Logo</h3>
            <div class="value"><?php echo count(array_filter($users, function($u) { return !empty($u['logo']); })); ?></div>
        </div>
        <div class="stat-card">
            <h3>With Background</h3>
            <div class="value"><?php echo count(array_filter($users, function($u) { return !empty($u['background_image']); })); ?></div>
        </div>
    </div>

    <div class="users-table-container">
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <h3>No Users Found</h3>
                <p>There are no users in the system yet.</p>
                <a href="create_user.php" class="create-btn" style="margin-top: 20px;">Create Your First User</a>
            </div>
        <?php else: ?>
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Logo</th>
                        <th>User Code</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Full Name</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <?php if (!empty($user['logo'])): ?>
                                    <img src="../<?php echo htmlspecialchars($user['logo']); ?>" alt="Logo" class="user-logo">
                                <?php else: ?>
                                    <div class="no-logo">No Logo</div>
                                <?php endif; ?>
                            </td>
                            <td><span class="user-code"><?php echo htmlspecialchars($user['user_code'] ?? '-'); ?></span></td>
                            <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($user['company'] ?? '-'); ?></td>
                            <td><span class="status-badge status-<?php echo htmlspecialchars($user['status']); ?>"><?php echo htmlspecialchars($user['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="action-btn edit-btn">Edit</a>
                                <a href="list_users.php?delete=<?php echo $user['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .page-header h2 {
        color: #333;
        font-size: 28px;
    }

    .create-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 25px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .create-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .stat-card h3 {
        color: #666;
        font-size: 14px;
        margin-bottom: 10px;
    }

    .stat-card .value {
        color: #333;
        font-size: 32px;
        font-weight: bold;
    }

    .users-table-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .users-table {
        width: 100%;
        border-collapse: collapse;
    }

    .users-table th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        text-align: left;
        font-weight: 500;
        font-size: 14px;
    }

    .users-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
        color: #333;
    }

    .users-table tr:hover {
        background: #f8f9fa;
    }

    .users-table tr:last-child td {
        border-bottom: none;
    }

    .user-logo {
        width: 50px;
        height: 50px;
        border-radius: 5px;
        object-fit: cover;
        background: #f0f0f0;
    }

    .no-logo {
        width: 50px;
        height: 50px;
        border-radius: 5px;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 12px;
    }

    .user-code {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        font-family: monospace;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }

    .status-active {
        background: #d4edda;
        color: #28a745;
    }

    .status-inactive {
        background: #f8d7da;
        color: #dc3545;
    }

    .status-suspended {
        background: #fff3cd;
        color: #856404;
    }

    .action-btn {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 12px;
        margin-right: 5px;
        transition: opacity 0.2s ease;
    }

    .action-btn:hover {
        opacity: 0.8;
    }

    .edit-btn {
        background: #28a745;
        color: white;
    }

    .delete-btn {
        background: #dc3545;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #666;
    }

    .empty-state h3 {
        margin-bottom: 10px;
        color: #333;
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }

        .users-table {
            font-size: 12px;
        }

        .users-table th,
        .users-table td {
            padding: 10px;
        }

        .stats-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// Include footer
require_once __DIR__ . '/includes/admin_footer.php';
?>
