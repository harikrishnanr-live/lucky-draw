<?php
/**
 * Client Lucky Draw Page
 * Shows the lucky draw page for authenticated client users
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['client_logged_in']) || $_SESSION['client_logged_in'] !== true) {
    header('Location: /client/login.php');
    exit;
}

// Get client data from session
$username = $_SESSION['client_username'];
$logo = $_SESSION['client_logo'];
$common_title = $_SESSION['client_common_title'] ?? 'Lucky Draw';
$year = $_SESSION['client_year'] ?? '2026';
$background_image = $_SESSION['client_background_image'];

// Set logo path (absolute path from root)
$logo_path = '/pbc_logo.jpeg'; // Default logo
if (!empty($logo)) {
    $logo_path = '/uploads/' . $logo;
}

// Set background image (absolute path from root)
$bg_style = '';
if (!empty($background_image)) {
    $bg_style = "background-image: url('/uploads/" . htmlspecialchars($background_image) . "');";
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>Lucky Draw - <?php echo htmlspecialchars($common_title . ' ' . $year); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      <?php echo $bg_style; ?>
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .mobile-menu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-in-out;
    }

    .mobile-menu.active {
      max-height: 300px;
    }

    .card-hover {
      transition: all 0.3s ease;
    }

    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
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

    .winner-animation {
      animation: winnerBounce 0.6s ease-in-out;
    }

    @keyframes winnerBounce {
      0% { transform: scale(0.3); opacity: 0; }
      50% { transform: scale(1.05); }
      70% { transform: scale(0.9); }
      100% { transform: scale(1); opacity: 1; }
    }
  </style>
</head>

<body class="bg-gray-900 bg-opacity-90 min-h-screen">
  <!-- Navigation -->
  <nav class="bg-gradient-to-r from-purple-800 to-indigo-800 text-white py-4 shadow-xl">
    <div class="max-w-7xl mx-auto flex justify-between items-center px-4">
      <a href="/client/dashboard.php" class="text-xl font-bold flex items-center space-x-2">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-full mb-4">
          <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Logo" class="w-full h-full object-cover rounded-full" />
        </div>
        <span><?php echo htmlspecialchars($common_title . ' ' . $year); ?></span>
      </a>

      <!-- Desktop Menu -->
      <div class="desktop-menu space-x-6 hidden md:flex">
        <a href="/client/dashboard.php" class="hover:text-gray-200 transition-colors duration-200 flex items-center space-x-1">
          <span>📋</span>
          <span>Upload Prize</span>
        </a>
        <a href="/client/upload-participants.php" class="hover:text-gray-200 transition-colors duration-200 flex items-center space-x-1">
          <span>👥</span>
          <span>Upload Participants</span>
        </a>
        <a href="/client/lucky-draw.php" class="font-semibold text-white bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-1">
          <span>🎲</span>
          <span>Lucky Draw</span>
        </a>
        <a href="/client/winners.php" class="hover:text-gray-200 transition-colors duration-200 flex items-center space-x-1">
          <span>🏆</span>
          <span>Winners</span>
        </a>
        <button id="navLogout" class="px-4 py-2 bg-white text-indigo-700 rounded-lg hover:bg-indigo-100 text-sm transition-colors duration-200 font-semibold">
          Logout
        </button>
      </div>

      <!-- Mobile Menu Button -->
      <button id="mobileMenuBtn" class="md:hidden focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu md:hidden bg-purple-900 bg-opacity-90 px-4 pb-4">
      <div class="flex flex-col space-y-3 mt-4">
        <a href="/client/dashboard.php" class="hover:text-gray-200 py-2 transition-colors duration-200">📋 Upload Prize</a>
        <a href="/client/upload-participants.php" class="hover:text-gray-200 py-2 transition-colors duration-200">👥 Upload Participants</a>
        <a href="/client/lucky-draw.php" class="font-semibold text-white bg-white bg-opacity-20 px-3 py-2 rounded-lg">🎲 Lucky Draw</a>
        <a href="/client/winners.php" class="hover:text-gray-200 py-2 transition-colors duration-200">🏆 Winners</a>
        <button id="mobileLogout" class="px-3 py-2 bg-white text-indigo-700 rounded-lg hover:bg-indigo-100 text-sm transition-colors duration-200 text-left font-semibold">
          Logout
        </button>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="max-w-4xl mx-auto mt-8 px-4">
    <!-- Page Header -->
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-white mb-2">🎲 Lucky Draw</h1>
      <p class="text-gray-300">
        Draw winners from your participants
      </p>
    </div>

    <!-- Lucky Draw Card -->
    <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-xl shadow-2xl p-8 mb-8 card-hover">
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">
          🎯 Draw Winners
        </h2>
        <p class="text-gray-600 text-sm">
          Select a prize and draw winners from your participants
        </p>
      </div>

      <div id="drawSection" class="text-center">
        <p class="text-gray-500 mb-4">Please upload prizes and participants first.</p>
        <a href="/client/dashboard.php" class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200">
          Upload Prizes
        </a>
      </div>
    </div>
  </div>

  <br />
  <!-- Footer Section -->
  <footer class="footer">
    <p>
      Design and Developed by
      <a href="https://arameglobal.com/" target="_blank">AraMeGlobal</a>
    </p>
  </footer>

  <!-- Scripts -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const expirationTime = localStorage.getItem("clientExpirationTime");
      const currentTime = new Date().toISOString();

      if (localStorage.getItem("clientUser") && expirationTime && new Date(currentTime) > new Date(expirationTime)) {
        window.location.href = "/client/login.php";
      }

      const logoutBtn = document.getElementById("navLogout");
      const mobileLogoutBtn = document.getElementById("mobileLogout");
      const mobileMenuBtn = document.getElementById("mobileMenuBtn");
      const mobileMenu = document.getElementById("mobileMenu");

      mobileMenuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("active");
      });

      const handleLogout = () => {
        localStorage.removeItem("clientUser");
        localStorage.removeItem("clientLoginTime");
        localStorage.removeItem("clientExpirationTime");
        window.location.href = "/client/logout.php";
      };

      logoutBtn.addEventListener("click", handleLogout);
      mobileLogoutBtn.addEventListener("click", handleLogout);
    });
  </script>
</body>

</html>
