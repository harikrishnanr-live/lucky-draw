<?php
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

// Check for error message
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Lucky Draw</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .login-header .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .login-header .logo i {
            font-size: 36px;
            color: white;
        }

        .login-header h1 {
            color: #1a1a2e;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #6b7280;
            font-size: 14px;
            font-weight: 400;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 500;
            font-size: 14px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .input-wrapper input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
            background: #f9fafb;
        }

        .input-wrapper input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .input-wrapper input:focus + i {
            color: #667eea;
        }

        .input-wrapper input::placeholder {
            color: #9ca3af;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9ca3af;
            cursor: pointer;
            padding: 4px;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 24px;
            border: 1px solid #fecaca;
            text-align: center;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .error-message i {
            font-size: 16px;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn i {
            font-size: 18px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .back-link:hover {
            color: #764ba2;
        }

        .back-link i {
            margin-right: 6px;
        }

        .validation-error {
            color: #dc2626;
            font-size: 12px;
            margin-top: 6px;
            display: none;
        }

        .validation-error.show {
            display: block;
        }

        .input-wrapper.error input {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .input-wrapper.error i {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-user-shield"></i>
            </div>
            <h1>Admin Login</h1>
            <p>Lucky Draw Management System</p>
        </div>

        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="authenticate.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="username">Email Address</label>
                <div class="input-wrapper" id="usernameWrapper">
                    <input type="email" id="username" name="username" placeholder="admin@arameglobal.com" required>
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="validation-error" id="usernameError">
                    <i class="fas fa-exclamation-circle"></i> Please enter a valid email address
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper" id="passwordWrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i class="fas fa-lock"></i>
                    <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div class="validation-error" id="passwordError">
                    <i class="fas fa-exclamation-circle"></i> Password is required
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Sign In
            </button>
        </form>

        <a href="../index.html" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Lucky Draw
        </a>
    </div>

    <script>
        // DOM Elements
        const loginForm = document.getElementById('loginForm');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const togglePasswordBtn = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');
        const usernameWrapper = document.getElementById('usernameWrapper');
        const passwordWrapper = document.getElementById('passwordWrapper');
        const usernameError = document.getElementById('usernameError');
        const passwordError = document.getElementById('passwordError');

        // Email validation regex
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        // Toggle password visibility
        togglePasswordBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            if (type === 'password') {
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            } else {
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            }
        });

        // Validate email on input
        usernameInput.addEventListener('input', function() {
            const email = this.value.trim();
            
            if (email && !emailRegex.test(email)) {
                usernameWrapper.classList.add('error');
                usernameError.classList.add('show');
            } else {
                usernameWrapper.classList.remove('error');
                usernameError.classList.remove('show');
            }
        });

        // Validate password on input
        passwordInput.addEventListener('input', function() {
            const password = this.value.trim();
            
            if (password) {
                passwordWrapper.classList.remove('error');
                passwordError.classList.remove('show');
            }
        });

        // Form submission validation
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Validate email
            const email = usernameInput.value.trim();
            if (!email || !emailRegex.test(email)) {
                usernameWrapper.classList.add('error');
                usernameError.classList.add('show');
                isValid = false;
            }
            
            // Validate password
            const password = passwordInput.value.trim();
            if (!password) {
                passwordWrapper.classList.add('error');
                passwordError.classList.add('show');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });

        // Clear errors on focus
        usernameInput.addEventListener('focus', function() {
            usernameWrapper.classList.remove('error');
            usernameError.classList.remove('show');
        });

        passwordInput.addEventListener('focus', function() {
            passwordWrapper.classList.remove('error');
            passwordError.classList.remove('show');
        });
    </script>
</body>
</html>
