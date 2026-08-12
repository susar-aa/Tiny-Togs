-- Database Alterations for Holiday Caching, Overrides, Roles, and History
USE `tiny_togs_roster`;

-- 1. Create cached_holidays table to cache API results
CREATE TABLE IF NOT EXISTS `cached_holidays` (
    `holiday_date` DATE PRIMARY KEY,
    `day_type` ENUM('Poya', 'Public Holiday') NOT NULL,
    `description` VARCHAR(100) NULL,
    `fetched_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Modify monthly_holidays day_type to allow 'Weekday' as a manual override
ALTER TABLE `monthly_holidays` MODIFY COLUMN `day_type` ENUM('Weekday', 'Poya', 'Public Holiday') NOT NULL;

-- 3. Create users table for administrator logins
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'Manager',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Add role column to users table if upgrading from older schema
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `role` VARCHAR(20) NOT NULL DEFAULT 'Manager';

-- Seed default user (admin / admin123)
INSERT INTO `users` (`username`, `password`, `role`) VALUES 
('admin', '$2y$10$wKxN0s3Kz/2T3Bq9cQ8H1O0XJb7jWbS9T2G4xP7Wz1U2H3r4y5t6u', 'Admin')
ON DUPLICATE KEY UPDATE `username` = `username`, `role` = 'Admin';

-- 5. Add role and skill_tag columns to employees table and migrate existing data
ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `role` ENUM('Rotating','Anchor','Cashier','Manager','Assistant_Manager') NOT NULL DEFAULT 'Rotating';
ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `skill_tag` ENUM('General','Keyholder','Manager-on-Duty') NOT NULL DEFAULT 'General';
ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `is_cashier` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `employees` ADD COLUMN IF NOT EXISTS `is_anchor` TINYINT(1) NOT NULL DEFAULT 0;

UPDATE `employees` SET `role` = 'Cashier' WHERE `is_cashier` = 1 AND `role` = 'Rotating';
UPDATE `employees` SET `role` = 'Anchor' WHERE `is_anchor` = 1 AND `role` = 'Rotating';

-- 6. Create roster_history table for undo/redo
CREATE TABLE IF NOT EXISTS `roster_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year` INT NOT NULL,
    `month` INT NOT NULL,
    `history_index` INT NOT NULL DEFAULT 0,
    `state_json` LONGTEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_year_month` (`year`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Create saved_rosters table
CREATE TABLE IF NOT EXISTS `saved_rosters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `year` INT NOT NULL,
    `month` INT NOT NULL,
    `saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_year_month` (`year`,`month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Create saved_roster_details table
CREATE TABLE IF NOT EXISTS `saved_roster_details` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `saved_roster_id` INT NOT NULL,
    `emp_id` INT NOT NULL,
    `date` DATE NOT NULL,
    `shift_code` VARCHAR(10) NOT NULL,
    `is_emergency_swap` TINYINT(1) NOT NULL DEFAULT 0,
    `swapped_with_emp_id` INT NULL,
    FOREIGN KEY (`saved_roster_id`) REFERENCES `saved_rosters` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE,
    FOREIGN KEY (`shift_code`) REFERENCES `shifts` (`shift_code`) ON UPDATE CASCADE,
    FOREIGN KEY (`swapped_with_emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE SET NULL,
    UNIQUE KEY `unique_saved_roster_emp_date` (`saved_roster_id`,`emp_id`,`date`),
    KEY `emp_id` (`emp_id`),
    KEY `shift_code` (`shift_code`),
    KEY `swapped_with_emp_id` (`swapped_with_emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
