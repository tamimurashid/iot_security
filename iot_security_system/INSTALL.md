# Installation Guide

## 1. Web Server Setup
1. Install a local development environment like XAMPP, WAMP, or MAMP.
2. Copy the entire `iot_security_system` folder into the `htdocs` (XAMPP) or `www` (WAMP) directory.

## 2. Database Setup
1. Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
2. Create a new database named `iot_security`.
3. Select the database and go to the "Import" tab.
4. Choose the `database.sql` file provided in the root directory and click "Go".
5. The default login for the web interface is:
   - **Username:** admin
   - **Password:** admin123

## 3. Configuration
1. Open `config/database.php` in a text editor.
2. Update the credentials if they differ from the defaults (root/root or root/blank).

## 4. Hardware Setup (ESP32)
1. Open the Arduino IDE.
2. Install the **ESP32** board package and the **ArduinoJson** library via the Library Manager.
3. Open `firmware/iot_security_node/iot_security_node.ino`.
4. Update the following variables at the top of the file:
   - `WIFI_SSID`: Your WiFi network name.
   - `WIFI_PASSWORD`: Your WiFi password.
   - `API_BASE_URL`: The URL to your PHP server (e.g., `http://192.168.1.100/iot_security_system/api/`).
5. Connect your sensors according to the pins defined in the code:
   - PIR Sensor -> GPIO 14
   - Laser Module -> GPIO 26
   - LDR Sensor -> GPIO 34
6. Upload the code to your ESP32.

## 5. Notification Setup
1. Log in to the web dashboard.
2. Go to the **Settings** page.
3. Enter your SMTP details for Email notifications.
4. Enter your Beam Africa API details for SMS notifications.
5. Check the "Enable" boxes and click "Save Settings".
