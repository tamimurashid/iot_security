-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Jun 26, 2026 at 07:13 PM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iot_security`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int NOT NULL,
  `alert_type` varchar(50) NOT NULL,
  `sensor_source` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'Unresolved',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `device_id` varchar(100) DEFAULT 'ESP32_NODE_01',
  `confidence` int DEFAULT '100',
  `sms_sent` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `alert_type`, `sensor_source`, `status`, `created_at`, `device_id`, `confidence`, `sms_sent`) VALUES
(1, 'Motion Intrusion', 'PIR', 'Resolved', '2026-06-25 12:54:57', 'ESP32_NODE_01', 100, 0),
(2, 'Motion Intrusion', 'PIR', 'Resolved', '2026-06-25 12:55:09', 'ESP32_NODE_01', 100, 0),
(3, 'Motion Intrusion', 'PIR', 'Unresolved', '2026-06-26 18:52:34', 'ESP32_NODE_01', 100, 0),
(4, 'Motion Intrusion', 'PIR', 'Cooldown', '2026-06-26 18:52:57', 'ESP32_NODE_01', 100, 0),
(5, 'Beam Break Intrusion', 'Laser', 'Unresolved', '2026-06-26 18:54:50', 'ESP32_NODE_01', 100, 0),
(6, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 18:54:54', 'ESP32_NODE_01', 100, 0),
(7, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 18:55:05', 'ESP32_NODE_01', 100, 0),
(8, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 18:55:08', 'ESP32_NODE_01', 100, 0),
(9, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 18:55:10', 'ESP32_NODE_01', 100, 0),
(10, 'Beam Break Intrusion', 'Laser', 'Unresolved', '2026-06-26 18:57:14', 'ESP32_NODE_01', 100, 0),
(11, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 18:57:24', 'ESP32_NODE_01', 100, 0),
(12, 'Beam Break Intrusion', 'Laser', 'Unresolved', '2026-06-26 19:00:13', 'ESP32_NODE_01', 100, 0),
(13, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 19:00:24', 'ESP32_NODE_01', 100, 0),
(14, 'Motion Intrusion', 'PIR', 'Unresolved', '2026-06-26 19:04:10', 'ESP32_NODE_01', 100, 0),
(15, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 19:04:18', 'ESP32_NODE_01', 100, 0),
(16, 'Motion Intrusion', 'PIR', 'Cooldown', '2026-06-26 19:04:36', 'ESP32_NODE_01', 100, 0),
(17, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 19:04:43', 'ESP32_NODE_01', 100, 0),
(18, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 19:05:09', 'ESP32_NODE_01', 100, 0),
(19, 'Beam Break Intrusion', 'Laser', 'Unresolved', '2026-06-26 19:06:09', 'ESP32_NODE_01', 100, 0),
(20, 'Motion Intrusion', 'PIR', 'Unresolved', '2026-06-26 19:11:16', 'ESP32_NODE_01', 100, 1),
(21, 'Beam Break Intrusion', 'Laser', 'Cooldown', '2026-06-26 19:11:27', 'ESP32_NODE_01', 100, 0),
(22, 'Beam Break Intrusion', 'Laser', 'Unresolved', '2026-06-26 19:12:19', 'ESP32_NODE_01', 100, 1);

-- --------------------------------------------------------

--
-- Table structure for table `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `api_keys`
--

INSERT INTO `api_keys` (`id`, `api_key`, `description`, `created_at`) VALUES
(1, 'YOUR_API_KEY', 'Default ESP32 Node API Key', '2026-06-15 07:19:57'),
(2, 'EggrollKey123', NULL, '2026-06-24 17:46:00');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-25 13:54:54'),
(2, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-25 13:55:15'),
(3, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-25 13:57:39'),
(4, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-25 13:59:47'),
(5, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-25 14:00:13'),
(6, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-25 14:02:54'),
(7, 1, 'User Login', 'Username: admin', '127.0.0.1', '2026-06-26 18:48:35'),
(8, 1, 'Alert Resolved', 'Alert ID: 2', '127.0.0.1', '2026-06-26 18:49:47'),
(9, 1, 'Alert Resolved', 'Alert ID: 1', '127.0.0.1', '2026-06-26 18:49:49'),
(10, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-26 18:54:16'),
(11, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-26 18:58:33'),
(12, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-26 18:58:56'),
(13, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-26 18:59:05'),
(14, 1, 'Test SMS Sent', 'Recipient: 255768857064', '127.0.0.1', '2026-06-26 19:05:19'),
(15, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-26 19:05:42'),
(16, 1, 'Settings Updated', NULL, '127.0.0.1', '2026-06-26 19:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `status` varchar(20) DEFAULT 'Offline',
  `pir_status` int DEFAULT '0',
  `laser_status` int DEFAULT '0',
  `ldr_value` int DEFAULT '0',
  `security_mode` varchar(20) DEFAULT 'Armed',
  `last_communication` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `device_name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `firmware_version` varchar(50) DEFAULT 'Unknown',
  `user_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `device_id`, `status`, `pir_status`, `laser_status`, `ldr_value`, `security_mode`, `last_communication`, `device_name`, `location`, `firmware_version`, `user_id`, `is_active`) VALUES
(1, 'ESP32_NODE_01', 'Online', 1, 0, 64, 'Armed', '2026-06-26 19:12:11', 'ESP32_NODE_01', NULL, 'Unknown', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `device_activity_logs`
--

CREATE TABLE `device_activity_logs` (
  `id` int NOT NULL,
  `device_id` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `device_activity_logs`
--

INSERT INTO `device_activity_logs` (`id`, `device_id`, `action`, `details`, `created_at`) VALUES
(1, 'ESP32_NODE_01', 'Alert Triggered', 'Beam Break Intrusion by Laser (Confidence: 100%)', '2026-06-26 18:54:50'),
(2, 'ESP32_NODE_01', 'Alert Triggered', 'Beam Break Intrusion by Laser (Confidence: 100%)', '2026-06-26 18:57:14'),
(3, 'ESP32_NODE_01', 'Alert Triggered', 'Motion Intrusion by PIR (Confidence: 100%)', '2026-06-26 19:04:11'),
(4, 'ESP32_NODE_01', 'Alert Triggered', 'Beam Break Intrusion by Laser (Confidence: 100%)', '2026-06-26 19:06:10'),
(5, 'ESP32_NODE_01', 'Alert Triggered', 'Motion Intrusion by PIR (Confidence: 100%)', '2026-06-26 19:11:17'),
(6, 'ESP32_NODE_01', 'Alert Triggered', 'Beam Break Intrusion by Laser (Confidence: 100%)', '2026-06-26 19:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int NOT NULL,
  `recipient` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'sms_api_url', 'https://api.beamafrica.com/v1/send'),
(2, 'sms_api_token', ''),
(3, 'sms_sender_name', 'dreamTek'),
(4, 'sms_recipient', '255768857064'),
(5, 'sms_enabled', '1'),
(6, 'smtp_host', 'smtp.gmail.com'),
(7, 'smtp_port', '587'),
(8, 'smtp_username', ''),
(9, 'smtp_password', ''),
(10, 'email_sender', 'alerts@iotsecurity.local'),
(11, 'email_sender_name', 'IoT Security'),
(12, 'email_recipient', ''),
(13, 'email_enabled', '0'),
(14, 'buzzer_mode_pir', 'beep'),
(15, 'buzzer_mode_laser', 'continuous'),
(16, 'buzzer_duration', '7000'),
(17, 'sms_api_key', '7bb3cb9f84ee62f7'),
(18, 'sms_secret_key', 'MGJkYzNkMzc0ZDU4ZjJjYjczODAzM2MzNjk2ODZlNWI3N2U5ODU1N2VhZTdjYjdmYWNhMTAxNWRhN2RiMWYyYQ=='),
(19, 'pir_sensitivity', 'medium'),
(20, 'detection_cooldown', '30'),
(21, 'motion_confirm_count', '1'),
(22, 'confidence_threshold', '50'),
(23, 'day_night_profile', 'auto'),
(24, 'alert_delay', '0'),
(25, 'alert_trigger_mode', 'both'),
(26, 'theme', 'dark');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int NOT NULL,
  `recipient` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `recipient`, `message`, `status`, `created_at`) VALUES
(1, '255768857064', '⚠ Security Alert! Motion detected at Unknown on 2026-06-26 19:04:10. Device: ESP32_NODE_01. Type: Motion Intrusion.', 'Failed - HTTP 400', '2026-06-26 19:04:11'),
(2, '255768857064', 'Test message from IoT Security System', 'Success', '2026-06-26 19:05:19'),
(3, '255768857064', '🚨 CRITICAL: Laser beam broken at Unknown on 2026-06-26 19:06:09. Device: ESP32_NODE_01. Confidence: 100%.', 'Failed - HTTP 400', '2026-06-26 19:06:10'),
(4, '255768857064', '⚠ Security Alert: Test Intrusion at Unknown. Device: ESP32_NODE_01. Time: 2026-06-26 19:10:26. Status: Active.', 'Success', '2026-06-26 19:10:27'),
(5, '255768857064', '⚠ Security Alert! Motion detected at Unknown on 2026-06-26 19:11:16. Device: ESP32_NODE_01. Type: Motion Intrusion.', 'Success', '2026-06-26 19:11:17'),
(6, '255768857064', '🚨 CRITICAL: Laser beam broken at Unknown on 2026-06-26 19:12:19. Device: ESP32_NODE_01. Confidence: 100%.', 'Success', '2026-06-26 19:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `sms_templates`
--

CREATE TABLE `sms_templates` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `template_body` text NOT NULL,
  `alert_type` varchar(50) DEFAULT 'all',
  `is_default` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sms_templates`
--

INSERT INTO `sms_templates` (`id`, `name`, `template_body`, `alert_type`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 'Motion Alert', '⚠ Security Alert! Motion detected at {location} on {date_time}. Device: {device_name}. Type: {alert_type}.', 'Motion Intrusion', 1, '2026-06-25 13:41:03', '2026-06-25 13:41:03'),
(2, 'Beam Break Alert', '🚨 CRITICAL: Laser beam broken at {location} on {date_time}. Device: {device_name}. Confidence: {confidence}%.', 'Beam Break Intrusion', 1, '2026-06-25 13:41:03', '2026-06-25 13:41:03'),
(3, 'General Alert', '⚠ Security Alert: {alert_type} at {location}. Device: {device_name}. Time: {date_time}. Status: {status}.', 'all', 0, '2026-06-25 13:41:03', '2026-06-25 13:41:03');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int NOT NULL,
  `action` varchar(255) NOT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `action`, `user_id`, `created_at`) VALUES
(1, 'User Logged In', 1, '2026-06-15 07:35:54'),
(2, 'User Logged In', 1, '2026-06-15 07:36:55'),
(3, 'User Logged In', 1, '2026-06-15 07:37:21'),
(4, 'System Mode Changed to Disarmed', 1, '2026-06-15 07:37:33'),
(5, 'User Logged In', 1, '2026-06-24 17:39:52'),
(6, 'System Mode Changed to Armed', 1, '2026-06-24 18:37:43'),
(7, 'User Logged In', 1, '2026-06-25 12:33:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `role` enum('admin','viewer') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `created_at`, `full_name`, `email`, `role`) VALUES
(1, 'admin', '$2y$12$BYLBt8fpQ3i7aXRhyb/8U.ep/oDGj2FKCbwFDGOyqtycVh6ObWTWK', '2026-06-15 07:19:57', 'Administrator', NULL, 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_created` (`created_at`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`);

--
-- Indexes for table `device_activity_logs`
--
ALTER TABLE `device_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_device_activity_device` (`device_id`),
  ADD KEY `idx_device_activity_created` (`created_at`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sms_templates`
--
ALTER TABLE `sms_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `device_activity_logs`
--
ALTER TABLE `device_activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=317;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sms_templates`
--
ALTER TABLE `sms_templates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
