CREATE DATABASE IF NOT EXISTS `iot_security`;
USE `iot_security`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` ENUM('admin','viewer') DEFAULT 'admin',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (Password is 'admin123' hashed with bcrypt)
INSERT INTO `users` (`username`, `password`, `full_name`, `role`) VALUES
('admin', '$2y$10$QxR8O8xN1t1R/lPqg4y6O.l3fT/w2N7S5m.1Qe6q/f8Jq1v9u1C.K', 'Administrator', 'admin')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- Settings table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
-- Beem Africa SMS Settings
('sms_api_key', ''),
('sms_secret_key', ''),
('sms_sender_name', 'INFO'),
('sms_recipient', ''),
('sms_enabled', '0'),
-- SMTP Email Settings (preserved from original)
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('email_sender', 'alerts@iotsecurity.local'),
('email_sender_name', 'IoT Security'),
('email_recipient', ''),
('email_enabled', '0'),
-- Device Buzzer Settings
('buzzer_mode_pir', 'beep'),
('buzzer_mode_laser', 'continuous'),
('buzzer_duration', '2000'),
-- Detection / False Positive Reduction Settings
('pir_sensitivity', 'medium'),
('detection_cooldown', '30'),
('motion_confirm_count', '1'),
('confidence_threshold', '50'),
('day_night_profile', 'auto'),
('alert_delay', '0'),
('alert_trigger_mode', 'both'),
-- UI Settings
('theme', 'dark')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

-- Alerts table
CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) DEFAULT 'ESP32_NODE_01',
  `alert_type` varchar(50) NOT NULL,
  `sensor_source` varchar(50) NOT NULL,
  `confidence` int(3) DEFAULT 100,
  `sms_sent` tinyint(1) DEFAULT 0,
  `status` varchar(20) DEFAULT 'Unresolved',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alerts_device` (`device_id`),
  KEY `idx_alerts_created` (`created_at`),
  KEY `idx_alerts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Devices table
CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `firmware_version` varchar(50) DEFAULT 'Unknown',
  `user_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` varchar(20) DEFAULT 'Offline',
  `pir_status` int(1) DEFAULT 0,
  `laser_status` int(1) DEFAULT 0,
  `ldr_value` int(11) DEFAULT 0,
  `security_mode` varchar(20) DEFAULT 'Armed',
  `last_communication` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_id` (`device_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default device
INSERT INTO `devices` (`device_id`, `device_name`, `location`, `status`, `security_mode`) VALUES
('ESP32_NODE_01', 'Main Security Node', 'Front Entrance', 'Offline', 'Armed')
ON DUPLICATE KEY UPDATE `device_id`=`device_id`;

-- SMS Logs table
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `response` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sms_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Email Logs table
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SMS Templates table
CREATE TABLE IF NOT EXISTS `sms_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `template_body` text NOT NULL,
  `alert_type` varchar(50) DEFAULT 'all',
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default SMS templates
INSERT INTO `sms_templates` (`name`, `template_body`, `alert_type`, `is_default`) VALUES
('Motion Alert', '⚠ Security Alert! Motion detected at {location} on {date_time}. Device: {device_name}. Type: {alert_type}.', 'Motion Intrusion', 1),
('Beam Break Alert', '🚨 CRITICAL: Laser beam broken at {location} on {date_time}. Device: {device_name}. Confidence: {confidence}%.', 'Beam Break Intrusion', 1),
('General Alert', '⚠ Security Alert: {alert_type} at {location}. Device: {device_name}. Time: {date_time}. Status: {status}.', 'all', 0)
ON DUPLICATE KEY UPDATE `name`=`name`;

-- Device Activity Logs table
CREATE TABLE IF NOT EXISTS `device_activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_device_activity_device` (`device_id`),
  KEY `idx_device_activity_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit Logs table (replaces old system_logs)
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Logs table (kept for backward compatibility)
CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(255) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- API Keys table
CREATE TABLE IF NOT EXISTS `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_key` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default API key
INSERT INTO `api_keys` (`api_key`, `description`) VALUES
('EggrollKey123', 'Default ESP32 Node API Key')
ON DUPLICATE KEY UPDATE `api_key`=`api_key`;
