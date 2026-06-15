# IoT Security Monitoring System

A complete final year project implementation of a Smart Security Solution using an ESP32, PHP, MySQL, and various sensors (PIR, Laser, LDR).

## Features
- **Intrusion Detection**: Detects motion (PIR) and physical beam breaks (Laser + LDR).
- **Notification Engine**: Sends automated alerts via SMS (Beam Africa API) and Email (PHPMailer).
- **Admin Dashboard**: Real-time monitoring of alerts, device status, and security modes.
- **REST API**: Secure, API-key protected endpoints for hardware communication.

## Hardware Components
- ESP32 Development Board
- PIR Motion Sensor (GPIO 14)
- Laser Module (GPIO 26)
- LDR Sensor (GPIO 34)

## Quick Start
1. Flash the ESP32 using the `.ino` file in the `firmware` folder.
2. Setup a local web server (XAMPP/MAMP) and import `database.sql`.
3. Configure `config/database.php` with your DB credentials.
4. Access the web dashboard (Default Login: admin / admin123).

See `INSTALL.md` for detailed instructions.
