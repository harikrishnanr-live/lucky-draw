-- Migration: Create users table for customers
-- Description: Creates a dedicated users table for customer management with unique codes
-- Created: 2026-03-28

-- Create users table for customers
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_code VARCHAR(20) NOT NULL UNIQUE,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(50),
    company VARCHAR(255),
    logo VARCHAR(500) NULL,
    common_title VARCHAR(255) NULL,
    year VARCHAR(10) NULL,
    background_image VARCHAR(500) NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_code (user_code),
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Note: This table stores customer users with unique codes like CUST001, CUST002
-- The user_code will be auto-generated when creating new users
