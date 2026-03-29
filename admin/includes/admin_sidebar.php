<?php
// Admin Sidebar Include
// This file should be included after the header in all admin pages
// Requires $currentPage variable to be set before including

$currentPage = isset($currentPage) ? $currentPage : '';
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h2>🎰 Lucky Draw</h2>
        <p>Admin Panel</p>
    </div>
    
    <ul class="sidebar-menu">
        <li>
            <a href="dashboard.php" <?php echo ($currentPage === 'dashboard') ? 'class="active"' : ''; ?>>
                <span class="menu-icon">📊</span>
                Dashboard
            </a>
        </li>
        <li>
            <a href="list_users.php" <?php echo ($currentPage === 'list_users') ? 'class="active"' : ''; ?>>
                <span class="menu-icon">👥</span>
                User Management
            </a>
        </li>
        <li>
            <a href="create_user.php" <?php echo ($currentPage === 'create_user') ? 'class="active"' : ''; ?>>
                <span class="menu-icon">➕</span>
                Create User
            </a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <div class="user-info">
            Logged in as: <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
        </div>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</div>

<div class="main-content">
