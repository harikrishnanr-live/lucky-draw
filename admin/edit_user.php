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
$userId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($userId === 0) {
    header('Location: list_users.php');
    exit();
}

// Fetch user data
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$user = null;

if ($conn->connect_error) {
    $message = 'Database connection failed: ' . $conn->connect_error;
    $messageType = 'error';
} else {
    $conn->set_charset("utf8mb4");
    
    $stmt = $conn->prepare("SELECT id, username, user_code, email, logo, common_title, year, background_image FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('Location: list_users.php');
        exit();
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $commonTitle = trim($_POST['common_title'] ?? '');
        $year = trim($_POST['year'] ?? '');
        $newPassword = $_POST['password'] ?? '';
        
        // Validate email
        if (empty($email)) {
            $message = 'Email is required.';
            $messageType = 'error';
        } else {
            // Handle file uploads
            $logoPath = $user['logo'];
            $backgroundPath = $user['background_image'];
            
            // Create uploads directory if it doesn't exist
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Upload logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logoName = 'logo_' . time() . '_' . basename($_FILES['logo']['name']);
                $logoTarget = $uploadDir . $logoName;
                
                // Validate image
                $logoCheck = getimagesize($_FILES['logo']['tmp_name']);
                if ($logoCheck !== false) {
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoTarget)) {
                        $logoPath = 'uploads/' . $logoName;
                    } else {
                        $message = 'Failed to upload logo image.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Logo must be a valid image file.';
                    $messageType = 'error';
                }
            }
            
            // Upload background image
            if (isset($_FILES['background_image']) && $_FILES['background_image']['error'] === UPLOAD_ERR_OK) {
                $bgName = 'bg_' . time() . '_' . basename($_FILES['background_image']['name']);
                $bgTarget = $uploadDir . $bgName;
                
                // Validate image
                $bgCheck = getimagesize($_FILES['background_image']['tmp_name']);
                if ($bgCheck !== false) {
                    if (move_uploaded_file($_FILES['background_image']['tmp_name'], $bgTarget)) {
                        $backgroundPath = 'uploads/' . $bgName;
                    } else {
                        $message = 'Failed to upload background image.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Background image must be a valid image file.';
                    $messageType = 'error';
                }
            }
            
            // If no errors, update user
            if (empty($message)) {
                if (!empty($newPassword)) {
                    // Update with new password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE admin_users SET email = ?, logo = ?, common_title = ?, year = ?, background_image = ?, password = ?, updated_at = NOW() WHERE id = ?");
                    $updateStmt->bind_param("ssssssi", $email, $logoPath, $commonTitle, $year, $backgroundPath, $hashedPassword, $userId);
                } else {
                    // Update without changing password
                    $updateStmt = $conn->prepare("UPDATE admin_users SET email = ?, logo = ?, common_title = ?, year = ?, background_image = ?, updated_at = NOW() WHERE id = ?");
                    $updateStmt->bind_param("sssssi", $email, $logoPath, $commonTitle, $year, $backgroundPath, $userId);
                }
                
                if ($updateStmt->execute()) {
                    $message = 'User updated successfully!';
                    $messageType = 'success';
                    
                    // Refresh user data
                    $stmt = $conn->prepare("SELECT id, username, user_code, email, logo, common_title, year, background_image FROM admin_users WHERE id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $user = $result->fetch_assoc();
                    $stmt->close();
                } else {
                    $message = 'Failed to update user: ' . $updateStmt->error;
                    $messageType = 'error';
                }
                $updateStmt->close();
            }
        }
    }
    
    $conn->close();
}

// Set current page for sidebar highlighting
$currentPage = 'list_users';
$pageTitle = 'Edit User';

// Include header
require_once __DIR__ . '/includes/admin_header.php';

// Include sidebar
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="header">
    <h1>Edit User</h1>
</div>

<div class="container">
    <div class="form-container">
        <h2>Edit User: <?php echo htmlspecialchars($user['username']); ?></h2>
        <p>Update the user details below.</p>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username</label>
                <div class="username-display"><?php echo htmlspecialchars($user['username']); ?></div>
                <div class="form-hint">Username cannot be changed</div>
            </div>

            <div class="form-group">
                <label>User Code</label>
                <div class="user-code-display"><?php echo htmlspecialchars($user['user_code'] ?? 'Not assigned'); ?></div>
                <div class="form-hint">Auto-generated unique customer code</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <div class="form-hint">Valid email address for the user</div>
                </div>

                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password">
                    <div class="form-hint">Leave blank to keep current password</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="common_title">Common Title</label>
                    <input type="text" id="common_title" name="common_title" value="<?php echo htmlspecialchars($user['common_title'] ?? ''); ?>" placeholder="e.g., Lucky Draw Event 2026">
                    <div class="form-hint">Optional: A common title to display across all pages</div>
                </div>

                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($user['year'] ?? ''); ?>" placeholder="e.g., 2026">
                    <div class="form-hint">Optional: Year for the user's context</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="logo">Logo Image</label>
                    <input type="file" id="logo" name="logo" accept="image/*">
                    <?php if (!empty($user['logo'])): ?>
                        <div class="current-image">
                            <strong>Current Logo:</strong><br>
                            <img src="../<?php echo htmlspecialchars($user['logo']); ?>" alt="Current Logo">
                        </div>
                    <?php endif; ?>
                    <div class="form-hint">Optional: Upload a new logo image (JPG, PNG, GIF)</div>
                </div>

                <div class="form-group">
                    <label for="background_image">Background Image</label>
                    <input type="file" id="background_image" name="background_image" accept="image/*">
                    <?php if (!empty($user['background_image'])): ?>
                        <div class="current-image">
                            <strong>Current Background:</strong><br>
                            <img src="../<?php echo htmlspecialchars($user['background_image']); ?>" alt="Current Background">
                        </div>
                    <?php endif; ?>
                    <div class="form-hint">Optional: Background image for all pages</div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Update User</button>
        </form>
    </div>
</div>

<style>
    .form-container {
        background: white;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-container h2 {
        color: #333;
        margin-bottom: 10px;
        font-size: 28px;
    }

    .form-container p {
        color: #666;
        margin-bottom: 30px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
        font-size: 14px;
    }

    .form-group label .required {
        color: #dc3545;
    }

    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="password"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 14px;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group input[type="password"]:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group input[type="file"] {
        width: 100%;
        padding: 10px;
        background: #f8f9fa;
        cursor: pointer;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .form-group input[type="file"]:hover {
        background: #e9ecef;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .current-image {
        margin-top: 10px;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
    }

    .current-image img {
        max-width: 100px;
        max-height: 100px;
        border-radius: 5px;
        margin-top: 5px;
    }

    .submit-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 14px 30px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        font-weight: 500;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        width: 100%;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .form-hint {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }

    .username-display {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 5px;
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }

    .user-code-display {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 15px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        font-family: monospace;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-container {
            padding: 25px;
        }
    }
</style>

<?php
// Include footer
require_once __DIR__ . '/includes/admin_footer.php';
?>
