<?php
/**
 * Client Winners Page
 * Shows the winners page for authenticated client users
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
  <title>Winners - <?php echo htmlspecialchars($common_title . ' ' . $year); ?></title>
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

    .table-container {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
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
        <a href="/client/lucky-draw.php" class="hover:text-gray-200 transition-colors duration-200 flex items-center space-x-1">
          <span>🎲</span>
          <span>Lucky Draw</span>
        </a>
        <a href="/client/winners.php" class="font-semibold text-white bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-1">
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
        <a href="/client/lucky-draw.php" class="hover:text-gray-200 py-2 transition-colors duration-200">🎲 Lucky Draw</a>
        <a href="/client/winners.php" class="font-semibold text-white bg-white bg-opacity-20 px-3 py-2 rounded-lg">🏆 Winners</a>
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
      <h1 class="text-3xl font-bold text-white mb-2">🏆 Winners</h1>
      <p class="text-gray-300">
        View all lucky draw winners
      </p>
    </div>

    <!-- Winners Table -->
    <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-xl shadow-2xl overflow-hidden table-container">
      <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
        <h3 class="text-lg font-semibold flex items-center">
          <span class="mr-2">🏆</span>
          Winner Information
        </h3>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <table id="winnerTable" class="hidden min-w-full">
          <thead class="bg-gray-50 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prize</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Winner Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ticket Number</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200"></tbody>
        </table>

        <div id="emptyState" class="p-8 text-center text-gray-500">
          <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <p class="text-sm">
            No winners yet. Run the lucky draw to select winners.
          </p>
        </div>
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

      // Load winners from localStorage
      const winners = JSON.parse(localStorage.getItem("winners") || "[]");
      if (winners.length) {
        renderWinnerTable(winners);
      }
    });

    function renderWinnerTable(data) {
      const table = document.getElementById("winnerTable");
      const tbody = table.querySelector("tbody");
      const emptyState = document.getElementById("emptyState");

      tbody.innerHTML = "";
      table.classList.remove("hidden");
      emptyState.classList.add("hidden");

      data.forEach((item) => {
        const row = document.createElement("tr");
        row.className = "hover:bg-gray-50 transition-colors duration-200";
        row.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${item.prize}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.winner}</td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${item.ticket}</td>
        `;
        tbody.appendChild(row);
      });
    }
  </script>
</body>

</html>
