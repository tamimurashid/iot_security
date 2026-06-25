<?php
// apply_sql.php
require_once 'config/database.php';

$queries = [
    // Users table
    "ALTER TABLE `users` ADD COLUMN `full_name` varchar(100) DEFAULT NULL",
    "ALTER TABLE `users` ADD COLUMN `email` varchar(255) DEFAULT NULL",
    "ALTER TABLE `users` ADD COLUMN `role` ENUM('admin','viewer') DEFAULT 'admin'",
    
    // Devices table
    "ALTER TABLE `devices` ADD COLUMN `device_name` varchar(100) DEFAULT NULL",
    "ALTER TABLE `devices` ADD COLUMN `location` varchar(255) DEFAULT NULL",
    "ALTER TABLE `devices` ADD COLUMN `firmware_version` varchar(50) DEFAULT 'Unknown'",
    "ALTER TABLE `devices` ADD COLUMN `user_id` int(11) DEFAULT NULL",
    "ALTER TABLE `devices` ADD COLUMN `is_active` tinyint(1) DEFAULT 1",
    
    // Alerts table
    "ALTER TABLE `alerts` ADD COLUMN `device_id` varchar(100) DEFAULT 'ESP32_NODE_01'",
    "ALTER TABLE `alerts` ADD COLUMN `confidence` int(3) DEFAULT 100",
    "ALTER TABLE `alerts` ADD COLUMN `sms_sent` tinyint(1) DEFAULT 0"
];

foreach ($queries as $query) {
    try {
        $pdo->exec($query);
        echo "Executed: $query\n";
    } catch (PDOException $e) {
        // Ignore duplicate column errors (SQLSTATE 42S21)
        if ($e->getCode() == '42S21') {
            echo "Skipped (already exists): $query\n";
        } else {
            echo "Error executing $query: " . $e->getMessage() . "\n";
        }
    }
}

// Now run the full database.sql to create any brand new tables and insert defaults
$sql = file_get_contents('database.sql');
try {
    $pdo->exec($sql);
    echo "Database schema baseline updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating database schema: " . $e->getMessage() . "\n";
}

// Update the admin user just in case
try {
    $pdo->exec("UPDATE `users` SET `full_name` = 'Administrator', `role` = 'admin' WHERE `username` = 'admin'");
} catch (PDOException $e) {}

// Update existing devices and alerts
try {
    $pdo->exec("UPDATE `devices` SET `device_name` = `device_id` WHERE `device_name` IS NULL");
    $pdo->exec("UPDATE `alerts` SET `device_id` = 'ESP32_NODE_01' WHERE `device_id` IS NULL");
} catch (PDOException $e) {}

