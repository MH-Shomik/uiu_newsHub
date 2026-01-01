-- =================================================================
-- PROJECT: Dynamic News and Alert System for UIU Students
-- DATABASE: uiu_news_system
-- SYSTEM: MySQL / MariaDB (XAMPP Compatible)
-- =================================================================

-- 1. Create and Select Database
CREATE DATABASE IF NOT EXISTS `uiu_news_system` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `uiu_news_system`;

-- Disable foreign key checks temporarily to ensure smooth resetting
SET FOREIGN_KEY_CHECKS = 0;

-- =================================================================
-- 2. TABLE STRUCTURES
-- =================================================================

-- Table: users
-- Stores admins, moderators, and student info
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL, -- Store hashed passwords here
  `role` ENUM('admin', 'moderator', 'student') DEFAULT 'student',
  `status` ENUM('active', 'banned') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: categories
-- Categorizes news (e.g., Academic, Research, Club Events)
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `slug` VARCHAR(50) NOT NULL UNIQUE, -- URL friendly name (e.g., academic-news)
  `color_class` VARCHAR(50) DEFAULT 'bg-gray-500', -- Tailwind class for badge color
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: news
-- The main news articles
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `news_id` INT(11) NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `category_id` INT(11) NOT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL, -- Path to image
  `is_urgent` TINYINT(1) DEFAULT 0, -- 1 = Flashing/Red border
  `status` ENUM('draft', 'published', 'archived') DEFAULT 'published',
  `views` INT(11) DEFAULT 0,
  `author_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`news_id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE CASCADE,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: alerts
-- Real-time alerts for Traffic, Weather, etc.
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `alert_id` INT(11) NOT NULL AUTO_INCREMENT,
  `type` ENUM('traffic', 'weather', 'national', 'emergency', 'campus') NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `message` VARCHAR(500) NOT NULL,
  `severity` ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
  `is_active` TINYINT(1) DEFAULT 1,
  `expires_at` DATETIME DEFAULT NULL, -- When the alert automatically disappears
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`alert_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: activity_logs (Optional but impressive for projects)
-- Tracks who posted what (Admin audit trail)
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `log_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL, -- e.g., "Posted News", "Deleted Alert"
  `details` TEXT DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =================================================================
-- 3. DUMMY DATA SEEDING (For UIU Context)
-- =================================================================

-- Users
-- Password for all is: 123456 (Hashed using BCRYPT for security simulation)
-- In a real PHP app, use password_verify() to check this.
INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `role`) VALUES
(1, 'System Admin', 'admin@uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
(2, 'Registrar Office', 'registrar@uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'moderator'),
(3, 'Rahim Student', 'rahim@ptr.uiu.ac.bd', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Categories
INSERT INTO `categories` (`category_id`, `name`, `slug`, `color_class`) VALUES
(1, 'Academic', 'academic', 'bg-blue-600'),
(2, 'Research & Grants', 'research', 'bg-purple-600'),
(3, 'Club Events', 'events', 'bg-orange-500'),
(4, 'Sports', 'sports', 'bg-green-600'),
(5, 'Notice Board', 'notice', 'bg-gray-700');

-- News
INSERT INTO `news` (`title`, `content`, `category_id`, `is_urgent`, `author_id`, `views`, `image_url`) VALUES
('Mid-Term Exam Schedule Fall 2025 Published', 'The mid-term examination schedule for Fall 2025 has been released. Students are requested to check their UCAM for room allocation.', 1, 1, 2, 150, 'https://placehold.co/600x400?text=Exam+Schedule'),
('UIU Mars Rover Team Wins 2nd Place in USA', 'Our brilliant engineering team has secured the 2nd runner-up position in the University Rover Challenge 2025 held in Utah.', 2, 0, 1, 340, 'https://placehold.co/600x400?text=Mars+Rover'),
('Bus Schedule Change for Tomorrow', 'Due to road construction on Madani Avenue, the 8:00 AM bus from Mirpur will depart 15 minutes early.', 5, 0, 2, 89, NULL),
('UIU IT Club Presents: Hackathon 2025', 'Registration is open for the biggest inter-university hackathon. Click here to register your team.', 3, 0, 3, 210, 'https://placehold.co/600x400?text=Hackathon');

-- Alerts (Real-time simulation)
INSERT INTO `alerts` (`type`, `title`, `message`, `severity`, `is_active`, `expires_at`) VALUES
('traffic', 'Heavy Traffic on Madani Avenue', 'Severe congestion reported near Notun Bazar entrance. Please use alternative routes or walking paths.', 'warning', 1, DATE_ADD(NOW(), INTERVAL 2 HOUR)),
('weather', 'Storm Warning', 'Heavy rainfall expected in the next 30 minutes. Shuttle bus services might be delayed.', 'info', 1, DATE_ADD(NOW(), INTERVAL 4 HOUR)),
('emergency', 'Server Maintenance', 'ELMS will be down for maintenance tonight from 12:00 AM to 4:00 AM.', 'danger', 0, DATE_ADD(NOW(), INTERVAL 1 DAY));

-- Activity Logs
INSERT INTO `activity_logs` (`user_id`, `action`, `details`) VALUES
(1, 'System Init', 'Database seeded with initial values'),
(2, 'Post Created', 'Posted Mid-Term Exam Schedule');