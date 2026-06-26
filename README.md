# IoT Security System

A comprehensive, end-to-end IoT Security System built with an **ESP32** microcontroller and a **PHP/MySQL** web backend. The system features multi-sensor intrusion detection (PIR motion and Laser beam-break), real-time device health monitoring, and instant alerts via SMS and Email.

---

## 🏗️ System Architecture

The project is divided into two main components:
1. **Hardware Node (Firmware):** Runs on an ESP32. It constantly monitors the environment using a PIR motion sensor and a Laser-LDR beam setup. It connects to the internet via WiFi and communicates with the server via HTTP JSON APIs.
2. **Web Dashboard (Backend):** A PHP-based centralized server that logs activities, manages device configurations, processes alerts, and dispatches notifications (SMS/Email) based on customized rules and cooldown mechanisms.

---

## 🛠️ Hardware Components

- **ESP32 Development Board** (Central controller with WiFi)
- **PIR Motion Sensor** (Detects human movement)
- **Laser Module & LDR (Light Dependent Resistor)** (Creates an invisible tripwire)
- **Active Buzzer** (For local alarms and beeps)
- **Jumper Wires & Breadboard**

---

## ✨ Key Features

- **Multi-Sensor Detection:** Monitors both motion and perimeter breaches simultaneously.
- **Dynamic WiFi Configuration:** Uses `WiFiManager` to easily set up WiFi credentials via a captive portal without hardcoding them into the firmware.
- **Hardware Reset:** Press and hold the BOOT button for 3 seconds to clear saved WiFi credentials and enter AP mode.
- **Intelligent Alerting:**
  - **SMS Notifications:** Integrates with the Beem Africa API. Emojis and non-ASCII characters are stripped automatically to ensure reliable delivery.
  - **Email Notifications:** Uses PHPMailer for HTML-formatted email alerts.
  - **Spam Prevention (Cooldowns):** Configurable cooldown windows prevent alert spam when sensors are triggered repeatedly.
- **Centralized Dashboard:** A sleek web interface to view logs, arm/disarm devices, configure API keys, and manage notification templates.
- **Device Health Monitoring:** The ESP32 sends periodic "heartbeats" to the server so you know if a node goes offline.

---

## ⚙️ Prerequisites

### For the Web Server (Backend)
- PHP (7.4 or 8.x)
- MySQL / MariaDB
- Composer (for any additional PHP dependencies, though PHPMailer is included)
- **Windows:** [XAMPP](https://www.apachefriends.org/) or WAMP
- **Linux:** Apache/Nginx + PHP-FPM + MySQL (LAMP/LEMP Stack)

### For the Firmware (ESP32)
- [Arduino IDE](https://www.arduino.cc/en/software) or `arduino-cli`
- ESP32 Board Package installed
- Arduino Libraries:
  - `WiFiManager`
  - `ArduinoJson`

---

## 🚀 Installation & Setup

### 1. Database Setup
1. Open your MySQL management tool (e.g., phpMyAdmin, MySQL Workbench).
2. Create a new database named `iot_security` (or a name of your choice).
3. Import the provided SQL dump:
   ```bash
   mysql -u root -p iot_security < iot_security_system/iot_security.sql
   ```

### 2. Server Setup

#### On Windows (Using XAMPP):
1. Copy the `iot_security_system` folder into your XAMPP `htdocs` directory (`C:\xampp\htdocs\`).
2. Open `config/database.php` and update the database credentials (username, password, dbname).
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Access the dashboard via `http://localhost/iot_security_system`.

#### On Linux (Ubuntu/Debian):
1. Install Apache, PHP, and MySQL:
   ```bash
   sudo apt update
   sudo apt install apache2 php libapache2-mod-php php-mysql mysql-server
   ```
2. Move the `iot_security_system` folder to `/var/www/html/`.
3. Set the correct permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/iot_security_system
   sudo chmod -R 755 /var/www/html/iot_security_system
   ```
4. Open `config/database.php` and configure your MySQL credentials.
5. Access the dashboard via your server's IP address (e.g., `http://YOUR_SERVER_IP/iot_security_system`).

### 3. Firmware Setup (ESP32)
1. Open `firmware/iot_security_node/iot_security_node.ino` in the Arduino IDE.
2. Update the `API_BASE_URL` variable to point to your server's local IP address or domain:
   ```cpp
   String API_BASE_URL = "http://YOUR_SERVER_IP/iot_security_system/api/";
   ```
3. Connect your ESP32 via USB and select the correct Port and Board (`ESP32 Dev Module`).
4. Compile and Upload the code.

---

## 📡 Configuration & Usage

### 1. Connecting the ESP32 to WiFi
- On first boot, the ESP32 will fail to find a saved network and start a WiFi Access Point named **`ESP32_Security_Node`**.
- Connect to this network using your phone or laptop.
- A captive portal will pop up. Select your home/office WiFi network and enter the password.
- The ESP32 will reboot and connect to your network.
- *To reset WiFi settings later, simply press and hold the BOOT button on the ESP32 for 3 seconds.*

### 2. Dashboard Settings
Log in to your web dashboard to configure the system:
- **SMS Configuration:** Navigate to Settings and input your Beem Africa API Key, Secret Key, and recipient phone number. Use the "Test SMS" button to verify your credentials.
- **Security Mode:** Arm or Disarm the system from the dashboard. When disarmed, the ESP32 will still log events, but SMS/Email notifications will be ignored.
- **Alert Trigger Mode:** Configure whether alarms should fire on PIR only, Laser only, or Both.

---

## 🐛 Troubleshooting

- **ESP32 Fails to Connect to Server:** Ensure there are no accidental spaces in your `API_BASE_URL` inside the firmware and that both the ESP32 and the server are on the same local network (if testing locally).
- **SMS Fails to Send:** If real alerts fail but the "Test SMS" works, check the SMS logs in the database. The system automatically strips emojis to prevent HTTP 400 Bad Request errors, but ensure your API limits haven't been reached.
- **Laser Triggering Inversely:** Ensure the LDR is wired properly (usually forming a voltage divider). The code expects the analog reading to be *above* the threshold when the beam is broken.

---
*Built with ❤️ for IoT Security & Home Automation. By Tamimu Rashid *
