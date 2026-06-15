<?php
// config/database.php

$host = '127.0.0.1';
$port = '8889'; // MAMP default MySQL port
$db   = 'iot_security';
$user = 'root';
$pass = 'root'; // User specified default
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, log this error instead of displaying it.
    header('HTTP/1.1 500 Internal Server Error');
    exit('Database connection failed: ' . $e->getMessage());
}
?>
