-- Migration: Add unique user code field to admin_users table
-- Description: Adds a unique customer code field (e.g., CUST001, CUST002) for each user
-- Created: 2026-03-28

-- Check and add user_code column if not exists
SET @dbname = DATABASE();
SET @tablename = 'admin_users';
SET @columnname = 'user_code';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (TABLE_SCHEMA = @dbname)
      AND (TABLE_NAME = @tablename)
      AND (COLUMN_NAME = @columnname)
  ) > 0,
  'SELECT 1',
  CONCAT('ALTER TABLE ', @tablename, ' ADD COLUMN ', @columnname, ' VARCHAR(20) NULL UNIQUE AFTER username')
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Note: This field will store unique codes like CUST001, CUST002, etc.
-- The code will be auto-generated when creating new users
