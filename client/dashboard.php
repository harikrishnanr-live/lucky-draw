<?php
/**
 * Client Dashboard Page
 * Shows the dashboard for authenticated client users
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
  <title>Upload Prize - <?php echo htmlspecialchars($common_title . ' ' . $year); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      <?php echo $bg_style; ?>
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    /* Professional Loading Spinner */
    .professional-loader {
      display: inline-block;
      position: relative;
      width: 32px;
      height: 32px;
      margin-right: 12px;
    }

    .professional-loader div {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: #6366f1;
      animation: professional-loader 1.2s linear infinite;
    }

    .professional-loader div:nth-child(1) { animation-delay: 0s; top: 37%; left: 66%; }
    .professional-loader div:nth-child(2) { animation-delay: -0.1s; top: 22%; left: 62%; }
    .professional-loader div:nth-child(3) { animation-delay: -0.2s; top: 11%; left: 52%; }
    .professional-loader div:nth-child(4) { animation-delay: -0.3s; top: 7%; left: 40%; }
    .professional-loader div:nth-child(5) { animation-delay: -0.4s; top: 11%; left: 28%; }
    .professional-loader div:nth-child(6) { animation-delay: -0.5s; top: 22%; left: 18%; }
    .professional-loader div:nth-child(7) { animation-delay: -0.6s; top: 37%; left: 14%; }
    .professional-loader div:nth-child(8) { animation-delay: -0.7s; top: 52%; left: 18%; }
    .professional-loader div:nth-child(9) { animation-delay: -0.8s; top: 68%; left: 28%; }
    .professional-loader div:nth-child(10) { animation-delay: -0.9s; top: 75%; left: 40%; }
    .professional-loader div:nth-child(11) { animation-delay: -1s; top: 68%; left: 52%; }
    .professional-loader div:nth-child(12) { animation-delay: -1.1s; top: 52%; left: 62%; }

    @keyframes professional-loader {
      0%, 20%, 80%, 100% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.5); opacity: 0.5; }
    }

    /* File Upload Animation */
    .file-upload-animation {
      animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }

    /* Success Animation */
    .success-animation {
      animation: successBounce 0.6s ease-in-out;
    }

    @keyframes successBounce {
      0% { transform: scale(0.3); opacity: 0; }
      50% { transform: scale(1.05); }
      70% { transform: scale(0.9); }
      100% { transform: scale(1); opacity: 1; }
    }

    /* Mobile menu styles */
    .mobile-menu {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-in-out;
    }

    .mobile-menu.active {
      max-height: 300px;
    }

    /* Card hover effects */
    .card-hover {
      transition: all 0.3s ease;
    }

    .card-hover:hover {
      transform: translateY(-2px);
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Table styling */
    .table-container {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }

    /* Footer Styles */
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
        <a href="/client/dashboard.php" class="font-semibold text-white bg-white bg-opacity-20 px-4 py-2 rounded-lg flex items-center space-x-1">
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
        <a href="/client/dashboard.php" class="font-semibold text-white bg-white bg-opacity-20 px-3 py-2 rounded-lg">📋 Upload Prize</a>
        <a href="/client/upload-participants.php" class="hover:text-gray-200 py-2 transition-colors duration-200">👥 Upload Participants</a>
        <a href="/client/lucky-draw.php" class="hover:text-gray-200 py-2 transition-colors duration-200">🎲 Lucky Draw</a>
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
      <h1 class="text-3xl font-bold text-white mb-2">📋 Upload Prize List</h1>
      <p class="text-gray-300">
        Upload your Excel file containing prize information
      </p>
    </div>

    <!-- Upload Card -->
    <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-xl shadow-2xl p-8 mb-8 card-hover">
      <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">
          📤 Excel File Upload
        </h2>
        <p class="text-gray-600 text-sm">
          Upload an .xlsx file with 2 columns:
          <span class="font-medium">Prize Name</span> and
          <span class="font-medium">Quantity</span>
        </p>
      </div>

      <div id="dropzone" class="mb-6 rounded-lg border-2 border-dashed border-indigo-400 bg-gradient-to-br from-indigo-50 to-purple-50 p-12 text-center text-gray-600 transition-all duration-300 ease-in-out hover:shadow-lg hover:from-indigo-100 hover:to-purple-100 cursor-pointer card-hover">
        <div class="flex flex-col items-center space-y-4">
          <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
          </div>
          <div>
            <p class="text-lg font-medium text-gray-700 mb-1">
              Drop your Excel file here
            </p>
            <p class="text-sm text-gray-500">
              or click to browse and select file
            </p>
          </div>
          <div class="flex items-center space-x-2 text-xs text-gray-400">
            <span>Supported format:</span>
            <span class="bg-green-100 text-green-700 px-2 py-1 rounded">.xlsx</span>
          </div>
        </div>
        <input type="file" id="excelInput" class="hidden" accept=".xlsx" />
      </div>

      <!-- Status Messages -->
      <div id="result" class="mb-4"></div>
    </div>

    <!-- Prize Table -->
    <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-xl shadow-2xl overflow-hidden table-container">
      <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
        <h3 class="text-lg font-semibold flex items-center">
          <span class="mr-2">🎁</span>
          Prize Information
        </h3>
      </div>

      <div class="max-h-96 overflow-y-auto">
        <table id="prizeTable" class="hidden min-w-full">
          <thead class="bg-gray-50 sticky top-0">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Prize Name
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Quantity
              </th>
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
            No prizes uploaded yet. Upload an Excel file to get started.
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
  <script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      // Check if user is already logged in and session is valid
      const expirationTime = localStorage.getItem("clientExpirationTime");
      const currentTime = new Date().toISOString();

      if (
        localStorage.getItem("clientUser") &&
        expirationTime &&
        new Date(currentTime) > new Date(expirationTime)
      ) {
        // Redirect to login if session has expired
        window.location.href = "/client/login.php";
      }

      const dropzone = document.getElementById("dropzone");
      const fileInput = document.getElementById("excelInput");
      const logoutBtn = document.getElementById("navLogout");
      const mobileLogoutBtn = document.getElementById("mobileLogout");
      const mobileMenuBtn = document.getElementById("mobileMenuBtn");
      const mobileMenu = document.getElementById("mobileMenu");

      // Mobile menu toggle
      mobileMenuBtn.addEventListener("click", () => {
        mobileMenu.classList.toggle("active");
      });

      // Dropzone interactions
      dropzone.addEventListener("click", () => fileInput.click());

      dropzone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropzone.classList.add("file-upload-animation");
        dropzone.style.borderColor = "#6366f1";
        dropzone.style.backgroundColor = "rgba(99, 102, 241, 0.1)";
      });

      dropzone.addEventListener("dragleave", () => {
        dropzone.classList.remove("file-upload-animation");
        dropzone.style.borderColor = "";
        dropzone.style.backgroundColor = "";
      });

      dropzone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropzone.classList.remove("file-upload-animation");
        dropzone.style.borderColor = "";
        dropzone.style.backgroundColor = "";
        handleFile(e.dataTransfer.files[0]);
      });

      fileInput.addEventListener("change", function () {
        handleFile(this.files[0]);
      });

      // Logout functionality
      const handleLogout = () => {
        localStorage.removeItem("clientUser");
        localStorage.removeItem("clientLoginTime");
        localStorage.removeItem("clientExpirationTime");
        window.location.href = "/client/logout.php";
      };

      logoutBtn.addEventListener("click", handleLogout);
      mobileLogoutBtn.addEventListener("click", handleLogout);

      // Load existing prize list on page load
      const stored = JSON.parse(localStorage.getItem("prizeList") || "[]");
      if (stored.length) {
        renderPrizeTable(stored);
        showMessage(
          "success",
          `✅ Loaded ${stored.length} prizes from storage.`,
        );
      }
    });

    function handleFile(file) {
      const result = document.getElementById("result");

      if (!file || !file.name.endsWith(".xlsx")) {
        showMessage("error", "❌ Please upload a valid .xlsx file.");
        return;
      }

      showMessage("loading", "Processing file...");

      // Clear previous data from localStorage
      localStorage.removeItem("prizeList");

      const reader = new FileReader();
      reader.onload = function (e) {
        try {
          const data = new Uint8Array(e.target.result);
          const workbook = XLSX.read(data, { type: "array" });
          const sheet = workbook.Sheets[workbook.SheetNames[0]];
          const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });

          const prizeData = rows
            .filter((row) => row[0] && !isNaN(row[1]))
            .map((row) => ({
              prize: row[0],
              qty: parseInt(row[1]),
              winners: [],
            }));

          if (prizeData.length === 0) {
            showMessage("error", "❌ No valid prize data found in the file.");
            return;
          }

          // Store in localStorage
          localStorage.setItem("prizeList", JSON.stringify(prizeData));

          // Render table
          renderPrizeTable(prizeData);

          showMessage(
            "success",
            `✅ Successfully loaded ${prizeData.length} prizes!`,
          );
        } catch (error) {
          showMessage("error", "❌ Error processing file: " + error.message);
        }
      };

      reader.readAsArrayBuffer(file);
    }

    function renderPrizeTable(data) {
      const table = document.getElementById("prizeTable");
      const tbody = table.querySelector("tbody");
      const emptyState = document.getElementById("emptyState");

      // Clear existing rows
      tbody.innerHTML = "";

      // Show table, hide empty state
      table.classList.remove("hidden");
      emptyState.classList.add("hidden");

      // Add rows
      data.forEach((item) => {
        const row = document.createElement("tr");
        row.className = "hover:bg-gray-50 transition-colors duration-200";
        row.innerHTML = `
          <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
            ${item.prize}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            ${item.qty}
          </td>
        `;
        tbody.appendChild(row);
      });
    }

    function showMessage(type, message) {
      const result = document.getElementById("result");
      result.innerHTML = "";

      const div = document.createElement("div");
      div.className = "p-4 rounded-lg ";

      if (type === "success") {
        div.className += "bg-green-100 text-green-800 border border-green-200";
      } else if (type === "error") {
        div.className += "bg-red-100 text-red-800 border border-red-200";
      } else if (type === "loading") {
        div.className += "bg-blue-100 text-blue-800 border border-blue-200";
        div.innerHTML = `<span class="professional-loader"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></span>${message}`;
        result.appendChild(div);
        return;
      }

      div.textContent = message;
      result.appendChild(div);
    }
  </script>
</body>

</html>
