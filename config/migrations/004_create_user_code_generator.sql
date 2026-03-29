-- Migration: Create user code generator for auto-generating unique codes
-- Description: Creates a function to generate codes like CUST001, CUST002
-- Created: 2026-03-28

-- Create a function to generate next user code
-- Note: This uses a simpler approach compatible with the migration runner

-- First, update existing users without user codes
UPDATE admin_users 
SET user_code = CONCAT('CUST', LPAD(
    (SELECT COUNT(*) FROM (SELECT id FROM admin_users WHERE user_code IS NOT NULL AND user_code != '') AS counted) + 1, 
    3, '0'
))
WHERE user_code IS NULL OR user_code = '';

-- Create a trigger-like mechanism using a simple approach
-- We'll handle code generation in the PHP application instead of using stored procedures
-- This is more compatible with the migration runner

-- Note: The user code generation will be handled in the create_user.php file
-- by querying the maximum existing code and incrementing it
