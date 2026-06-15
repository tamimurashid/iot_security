CREATE DATABASE IF NOT EXISTS `iot_security`;
USE `iot_security`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (Password is 'admin123' hashed with bcrypt)
INSERT INTO `users` (`username`, `password`) VALUES
('admin', '$2y$10$QxR8O8xN1t1R/lPqg4y6O.l3fT/w2N7S5m.1Qe6q/f8Jq1v9u1C.K')
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
('sms_api_url', 'https://api.beamafrica.com/v1/send'),
('sms_api_token', ''),
('sms_sender_name', 'IOTSEC'),
('sms_recipient', ''),
('sms_enabled', '0'),
('smtp_host', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('email_sender', 'alerts@iotsecurity.local'),
('email_sender_name', 'IoT Security'),
('email_recipient', ''),
('email_enabled', '0')
ON DUPLICATE KEY UPDATE `setting_key`=`setting_key`;

-- Alerts table
CREATE TABLE IF NOT EXISTS `alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alert_type` varchar(50) NOT NULL,
  `sensor_source` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'Unresolved',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Devices table (for tracking ESP32 status)
CREATE TABLE IF NOT EXISTS `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` varchar(100) NOT NULL,
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
INSERT INTO `devices` (`device_id`, `status`, `security_mode`) VALUES
('ESP32_NODE_01', 'Offline', 'Armed')
ON DUPLICATE KEY UPDATE `device_id`=`device_id`;

-- SMS Logs table
CREATE TABLE IF NOT EXISTS `sms_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
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

-- System Logs table
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
('YOUR_API_KEY', 'Default ESP32 Node API Key')
ON DUPLICATE KEY UPDATE `api_key`=`api_key`;
