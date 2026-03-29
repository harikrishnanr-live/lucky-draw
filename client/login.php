<?php
/**
 * Client Login Page
 * Dynamic login page for each client based on username from URL
 * Usage: http://localhost:8000/{username}
 */

// Check if database connection already exists (from router.php)
if (!isset($conn) || !($conn instanceof mysqli)) {
    require_once __DIR__ . '/../includes/db_connect.php';
}

// Start session to get error messages
session_start();

// Get username from URL path
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/');
$username = $path;

// Validate username exists in database
$user = null;
$logo_path = '/pbc_logo.jpeg'; // Default logo (absolute path from root)
$common_title = 'Lucky Draw';
$year = '2026';
$background_image = '';

if (!empty($username)) {
    $stmt = $conn->prepare("SELECT id, username, logo, common_title, year, background_image, status FROM users WHERE username = ? AND status = 'active'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set custom logo if exists (use absolute path from root)
        if (!empty($user['logo'])) {
            $logo_path = '/uploads/' . $user['logo'];
        }
        
        // Set custom title if exists
        if (!empty($user['common_title'])) {
            $common_title = $user['common_title'];
        }
        
        // Set custom year if exists
        if (!empty($user['year'])) {
            $year = $user['year'];
        }
        
        // Set custom background if exists (use absolute path from root)
        if (!empty($user['background_image'])) {
            $background_image = '/uploads/' . $user['background_image'];
        }
    }
    $stmt->close();
}

// If user not found, show error
if (!$user) {
    http_response_code(404);
    echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>404 - User Not Found</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-gray-900 min-h-screen flex items-center justify-center'>
        <div class='text-center'>
            <h1 class='text-4xl font-bold text-white mb-4'>404</h1>
            <p class='text-gray-300 mb-4'>User not found: " . htmlspecialchars($username) . "</p>
            <a href='/' class='text-blue-400 hover:text-blue-300'>Go to Home</a>
        </div>
    </body>
    </html>";
    exit;
}

// Store username in session for later use
$_SESSION['client_username'] = $username;
$_SESSION['client_user_id'] = $user['id'];

// Get error message from session if exists
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Clear error message after reading
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($common_title . ' ' . $year); ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      <?php if (!empty($background_image)): ?>
      background-image: url('<?php echo htmlspecialchars($background_image); ?>');
      <?php endif; ?>
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }

    .login-container {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 400px;
      padding: 2rem;
      border-radius: 1rem;
    }

    .input-group {
      position: relative;
    }

    .input-field {
      transition: all 0.3s ease;
    }

    .input-field:focus {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
    }

    .login-btn {
      background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);
      transition: all 0.3s ease;
    }

    .login-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
    }

    .login-btn:active {
      transform: translateY(0);
    }

    .login-btn:disabled {
      opacity: 0.7;
      cursor: not-allowed;
      transform: none;
    }

    .error-shake {
      animation: shake 0.5s ease-in-out;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    .fade-in {
      opacity: 0;
      transform: translateY(20px);
      animation: fadeIn 0.6s ease-out forwards;
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .loading-spinner {
      border: 2px solid #f3f4f6;
      border-top: 2px solid #3b82f6;
      border-radius: 50%;
      width: 16px;
      height: 16px;
      animation: spin 1s linear infinite;
      display: inline-block;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    .footer {
      background-color: #5727a6;
      padding: 20px;
      text-align: center;
      border-top: 1px solid rgba(0, 0, 0, 0.1);
      position: relative;
      bottom: 0;
      left: 0;
      right: 0;
      width: 100%;
      z-index: 10;
    }

    .footer p {
      margin: 0;
      color: white;
      font-size: 0.875rem;
    }

    .footer a {
      color: #3b82f6;
      text-decoration: none;
    }
  </style>
</head>

<body>

  <!-- Background overlay -->
  <div class="absolute inset-0 bg-gradient-to-br from-blue-900/20 to-indigo-900/20"></div>

  <div class="relative z-10 w-full max-w-md mx-auto">
    <div class="login-container rounded-2xl p-8 fade-in">

      <!-- Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full mb-4">
          <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" class="w-full h-full object-cover rounded-full" />
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
          <?php echo htmlspecialchars($common_title); ?>
        </h1>
        <p class="text-gray-600 text-sm">
          <?php echo htmlspecialchars($year); ?>
        </p>
      </div>

      <!-- Login Form -->
      <form id="loginForm" class="space-y-6" action="/client/authenticate.php" method="POST">
        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">

        <!-- Password Field -->
        <div class="input-group">
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-lock mr-2 text-blue-600"></i>
            Password
          </label>
          <div class="relative">
            <input type="password" id="password" name="password" placeholder="Enter your password"
              class="input-field w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              autocomplete="current-password" required />
            <button type="button" id="togglePassword"
              class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600 transition-colors duration-200">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div id="passwordError" class="text-red-500 text-xs mt-1 hidden">
            <i class="fas fa-exclamation-circle mr-1"></i>
            Password is required
          </div>
        </div>

        <!-- Error Message from Session -->
        <?php if (!empty($error_message)): ?>
        <div id="loginError" class="text-red-500 text-sm p-3 bg-red-50 border border-red-200 rounded-lg">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php else: ?>
        <div id="loginError" class="text-red-500 text-sm p-3 bg-red-50 border border-red-200 rounded-lg hidden">
          <i class="fas fa-exclamation-triangle mr-2"></i>
          Invalid password. Please try again.
        </div>
        <?php endif; ?>

        <!-- Login Button -->
        <button type="submit" id="loginBtn"
          class="login-btn w-full text-white py-3 px-6 rounded-lg font-semibold text-sm uppercase tracking-wide">
          <span id="loginBtnText">
            <i class="fas fa-sign-in-alt mr-2"></i>
            Sign In
          </span>
          <span id="loginBtnLoading" class="hidden">
            <span class="loading-spinner mr-2"></span>
            Authenticating...
          </span>
        </button>

      </form>

      <!-- Footer -->
      <div class="mt-8 text-center">
        <p class="text-xs text-gray-500">
          <i class="fas fa-shield-alt mr-1"></i>
          Secure Client Access Only
        </p>
      </div>

    </div>
  </div>

  <!-- Scripts -->
  <script>
    // DOM Elements
    const loginForm = document.getElementById("loginForm");
    const passwordInput = document.getElementById("password");
    const togglePasswordBtn = document.getElementById("togglePassword");
    const eyeIcon = document.getElementById("eyeIcon");
    const loginBtn = document.getElementById("loginBtn");
    const loginBtnText = document.getElementById("loginBtnText");
    const loginBtnLoading = document.getElementById("loginBtnLoading");
    const passwordError = document.getElementById("passwordError");
    const loginError = document.getElementById("loginError");

    // Clear all error messages
    function clearErrors() {
      passwordError.classList.add("hidden");
      loginError.classList.add("hidden");
      passwordInput.classList.remove("border-red-500", "error-shake");
    }

    // Show specific error
    function showError(element, errorDiv) {
      errorDiv.classList.remove("hidden");
      element.classList.add("border-red-500", "error-shake");
      element.focus();

      setTimeout(() => {
        element.classList.remove("error-shake");
      }, 500);
    }

    // Toggle password visibility
    function togglePassword() {
      const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);

      if (type === "password") {
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
      } else {
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
      }
    }

    // Show loading state
    function showLoading() {
      loginBtn.disabled = true;
      loginBtnText.classList.add("hidden");
      loginBtnLoading.classList.remove("hidden");
    }

    // Hide loading state
    function hideLoading() {
      loginBtn.disabled = false;
      loginBtnText.classList.remove("hidden");
      loginBtnLoading.classList.add("hidden");
    }

    // Validate form
    function validateForm() {
      clearErrors();

      const password = passwordInput.value.trim();
      let isValid = true;

      if (!password) {
        showError(passwordInput, passwordError);
        isValid = false;
      }

      return isValid;
    }

    // Event Listeners
    loginForm.addEventListener("submit", (e) => {
      if (!validateForm()) {
        e.preventDefault();
        return;
      }
      showLoading();
    });

    togglePasswordBtn.addEventListener("click", togglePassword);

    // Clear errors on input
    passwordInput.addEventListener("input", clearErrors);

    // Enter key support
    passwordInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        login();
      }
    });

    // Check if user is already logged in and session is valid
    document.addEventListener("DOMContentLoaded", () => {
      const expirationTime = localStorage.getItem("clientExpirationTime");
      const currentTime = new Date().toISOString();

      if (localStorage.getItem("clientUser") && expirationTime && new Date(currentTime) < new Date(expirationTime)) {
        window.location.href = "/client/dashboard.php";
      } else {
        localStorage.removeItem("clientUser");
        localStorage.removeItem("clientLoginTime");
        localStorage.removeItem("clientExpirationTime");
      }
    });
  </script>
</body>

</html>
