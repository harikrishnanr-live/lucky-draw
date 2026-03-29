-- Lucky Draw Database Setup Script
-- Run this script to create the database and tables

-- Create database
CREATE DATABASE IF NOT EXISTS lucky_draw;
USE lucky_draw;

-- Create admin_users table (optional - for future database-based authentication)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    user_code VARCHAR(20) NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    logo VARCHAR(500) NULL,
    common_title VARCHAR(255) NULL,
    year VARCHAR(10) NULL,
    background_image VARCHAR(500) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_code (user_code)
);

-- Create participants table
CREATE TABLE IF NOT EXISTS participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(50),
    ticket_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create prizes table
CREATE TABLE IF NOT EXISTS prizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    quantity INT DEFAULT 1,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create winners table
CREATE TABLE IF NOT EXISTS winners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT,
    prize_id INT,
    draw_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id),
    FOREIGN KEY (prize_id) REFERENCES prizes(id)
);

-- Create draws table
CREATE TABLE IF NOT EXISTS draws (
    id INT AUTO_INCREMENT PRIMARY KEY,
    draw_name VARCHAR(255),
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin@arameglobal.com)
-- Note: In production, use password_hash() for secure password storage
INSERT INTO admin_users (username, password, email) 
VALUES ('admin@arameglobal.com', 'admin@arameglobal.com', 'admin@arameglobal.com')
ON DUPLICATE KEY UPDATE username = username;

-- Sample data for testing (optional)
-- INSERT INTO participants (name, email, phone, ticket_number) VALUES
-- ('John Doe', 'john@example.com', '1234567890', 'TICKET001'),
-- ('Jane Smith', 'jane@example.com', '0987654321', 'TICKET002');

-- INSERT INTO prizes (name, description, quantity) VALUES
-- ('Grand Prize', 'Main prize for the lucky draw', 1),
-- ('Second Prize', 'Second prize', 2),
-- ('Third Prize', 'Third prize', 3);
