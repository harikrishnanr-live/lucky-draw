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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        $message = 'Database connection failed: ' . $conn->connect_error;
        $messageType = 'error';
    } else {
        $conn->set_charset("utf8mb4");
        
        // Get form data
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $email = trim($_POST['email'] ?? '');
        $commonTitle = trim($_POST['common_title'] ?? '');
        $year = trim($_POST['year'] ?? '');
        
        // Validate required fields
        if (empty($username) || empty($password) || empty($email)) {
            $message = 'Username, password, and email are required fields.';
            $messageType = 'error';
        } else {
            // Check if username already exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $checkStmt->bind_param("s", $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $message = 'Username already exists. Please choose a different username.';
                $messageType = 'error';
            } else {
                // Check if email already exists
                $emailStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $emailStmt->bind_param("s", $email);
                $emailStmt->execute();
                $emailResult = $emailStmt->get_result();
                
                if ($emailResult->num_rows > 0) {
                    $message = 'Email already exists. Please use a different email.';
                    $messageType = 'error';
                } else {
                    // Handle file uploads
                    $logoPath = null;
                    $backgroundPath = null;
                    
                    // Create uploads directory if it doesn't exist
                    $uploadDir = __DIR__ . '/../uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    // Get base URL for full links
                    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'];
                    $baseUrl = $protocol . '://' . $host;
                    
                    // Upload logo
                    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                        $logoName = 'logo_' . time() . '_' . basename($_FILES['logo']['name']);
                        $logoTarget = $uploadDir . $logoName;
                        
                        // Validate image
                        $logoCheck = getimagesize($_FILES['logo']['tmp_name']);
                        if ($logoCheck !== false) {
                            if (move_uploaded_file($_FILES['logo']['tmp_name'], $logoTarget)) {
                                $logoPath = $baseUrl . '/uploads/' . $logoName;
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
                                $backgroundPath = $baseUrl . '/uploads/' . $bgName;
                            } else {
                                $message = 'Failed to upload background image.';
                                $messageType = 'error';
                            }
                        } else {
                            $message = 'Background image must be a valid image file.';
                            $messageType = 'error';
                        }
                    }
                    
                    // If no errors, insert user
                    if (empty($message)) {
                        // Hash password
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        
                        // Generate unique user code
                        $maxCodeStmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(user_code, 5) AS UNSIGNED)) as max_num FROM users WHERE user_code REGEXP '^CUST[0-9]+$'");
                        $maxCodeStmt->execute();
                        $maxCodeResult = $maxCodeStmt->get_result();
                        $maxCodeRow = $maxCodeResult->fetch_assoc();
                        $maxNum = $maxCodeRow['max_num'] ?? 0;
                        $nextNum = $maxNum + 1;
                        $userCode = 'CUST' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
                        $maxCodeStmt->close();
                        
                        // Insert user with generated code
                        $insertStmt = $conn->prepare("INSERT INTO users (username, user_code, password, email, logo, common_title, year, background_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $insertStmt->bind_param("ssssssss", $username, $userCode, $hashedPassword, $email, $logoPath, $commonTitle, $year, $backgroundPath);
                        
                        if ($insertStmt->execute()) {
                            $message = 'User created successfully! User Code: ' . htmlspecialchars($userCode);
                            $messageType = 'success';
                            
                            // Clear form data
                            $username = '';
                            $email = '';
                            $commonTitle = '';
                            $year = '';
                        } else {
                            $message = 'Failed to create user: ' . $insertStmt->error;
                            $messageType = 'error';
                        }
                        
                        $insertStmt->close();
                    }
                }
                
                $emailStmt->close();
            }
            
            $checkStmt->close();
        }
        
        $conn->close();
    }
}

// Set current page for sidebar highlighting
$currentPage = 'create_user';
$pageTitle = 'Create User';

// Include header
require_once __DIR__ . '/includes/admin_header.php';

// Include sidebar
require_once __DIR__ . '/includes/admin_sidebar.php';
?>

<div class="header">
    <h1>Create New User</h1>
</div>

<div class="container">
    <div class="form-container">
        <h2>Create New User</h2>
        <p>Fill in the details below to create a new user account.</p>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    <div class="form-hint">Choose a unique username for the new user</div>
                </div>

                <div class="form-group">
                    <label for="email">Email <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    <div class="form-hint">Valid email address for the user</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" required>
                    <div class="form-hint">Password will be encrypted using bcrypt</div>
                </div>

                <div class="form-group">
                    <label for="year">Year</label>
                    <input type="text" id="year" name="year" value="<?php echo htmlspecialchars($year ?? ''); ?>" placeholder="e.g., 2026">
                    <div class="form-hint">Optional: Year for the user's context</div>
                </div>
            </div>

            <div class="form-group">
                <label for="common_title">Common Title</label>
                <input type="text" id="common_title" name="common_title" value="<?php echo htmlspecialchars($commonTitle ?? ''); ?>" placeholder="e.g., Lucky Draw Event 2026">
                <div class="form-hint">Optional: A common title to display across all pages</div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="logo">Logo Image</label>
                    <div class="drop-zone" id="logo-drop-zone">
                        <div class="drop-zone-content">
                            <span class="drop-zone-icon">📁</span>
                            <p>Drag & drop logo image here</p>
                            <p class="drop-zone-hint">or click to browse</p>
                        </div>
                        <input type="file" id="logo" name="logo" accept="image/*" class="drop-zone-input">
                        <div class="drop-zone-preview" id="logo-preview"></div>
                    </div>
                    <div class="form-hint">Optional: Upload a logo image (JPG, PNG, GIF)</div>
                </div>

                <div class="form-group">
                    <label for="background_image">Background Image</label>
                    <div class="drop-zone" id="bg-drop-zone">
                        <div class="drop-zone-content">
                            <span class="drop-zone-icon">🖼️</span>
                            <p>Drag & drop background image here</p>
                            <p class="drop-zone-hint">or click to browse</p>
                        </div>
                        <input type="file" id="background_image" name="background_image" accept="image/*" class="drop-zone-input">
                        <div class="drop-zone-preview" id="bg-preview"></div>
                    </div>
                    <div class="form-hint">Optional: Background image for all pages</div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Create User</button>
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
    .form-group input[type="password"],
    .form-group input[type="file"] {
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
        padding: 10px;
        background: #f8f9fa;
        cursor: pointer;
    }

    .form-group input[type="file"]:hover {
        background: #e9ecef;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
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

    .drop-zone {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fafafa;
        position: relative;
        min-height: 150px;
    }

    .drop-zone:hover {
        border-color: #667eea;
        background: #f0f4ff;
    }

    .drop-zone.drag-over {
        border-color: #667eea;
        background: #e8eeff;
        transform: scale(1.02);
    }

    .drop-zone-content {
        pointer-events: none;
    }

    .drop-zone-icon {
        font-size: 48px;
        margin-bottom: 10px;
        display: block;
    }

    .drop-zone-content p {
        margin: 5px 0;
        color: #666;
    }

    .drop-zone-hint {
        font-size: 12px;
        color: #999;
    }

    .drop-zone-input {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
    }

    .drop-zone-preview {
        margin-top: 15px;
        display: none;
    }

    .drop-zone-preview img {
        max-width: 100%;
        max-height: 150px;
        border-radius: 5px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .drop-zone-preview .file-name {
        font-size: 12px;
        color: #666;
        margin-top: 8px;
        word-break: break-all;
    }

    .drop-zone-preview .remove-btn {
        background: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 11px;
        cursor: pointer;
        margin-top: 8px;
    }

    .drop-zone-preview .remove-btn:hover {
        background: #c82333;
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .form-container {
            padding: 25px;
        }

        .drop-zone {
            padding: 20px 15px;
            min-height: 120px;
        }

        .drop-zone-icon {
            font-size: 36px;
        }
    }
</style>

<script>
    // Drag and drop functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize drop zones
        initDropZone('logo-drop-zone', 'logo', 'logo-preview');
        initDropZone('bg-drop-zone', 'background_image', 'bg-preview');
    });

    function initDropZone(dropZoneId, inputId, previewId) {
        const dropZone = document.getElementById(dropZoneId);
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);

        if (!dropZone || !input || !preview) return;

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);

        // Handle file input change
        input.addEventListener('change', handleFiles, false);

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('drag-over');
        }

        function unhighlight(e) {
            dropZone.classList.remove('drag-over');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                input.files = files;
                handleFiles();
            }
        }

        function handleFiles() {
            const files = input.files;
            
            if (files.length > 0) {
                const file = files[0];
                
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please upload an image file (JPG, PNG, GIF)');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                        <div class="file-name">${file.name}</div>
                        <button type="button" class="remove-btn" onclick="removeFile('${inputId}', '${previewId}')">Remove</button>
                    `;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    }

    function removeFile(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
        if (input) input.value = '';
        if (preview) {
            preview.innerHTML = '';
            preview.style.display = 'none';
        }
    }
</script>

<?php
// Include footer
require_once __DIR__ . '/includes/admin_footer.php';
?>
