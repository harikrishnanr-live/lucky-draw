<?php
// Admin Header Include
// This file should be included at the top of all admin pages
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin Dashboard' : 'Admin Dashboard'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            min-height: 100vh;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            height: 100vh;
            background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .sidebar-menu a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 3px solid white;
        }

        .sidebar-menu .menu-icon {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            padding: 0 20px;
        }

        .sidebar-footer .user-info {
            font-size: 12px;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .sidebar-footer .logout-btn {
            display: block;
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            text-align: center;
            transition: background 0.3s ease;
        }

        .sidebar-footer .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }

        .header {
            background: white;
            color: #333;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 24px;
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .message {
            padding: 15px 20px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
