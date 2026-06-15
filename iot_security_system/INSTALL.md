# Installation and Configuration Guide

## 1. Web Server Setup
1. Install a local development environment like XAMPP, WAMP, or MAMP (or use a built-in PHP server).
2. Copy the entire `iot_security_system` folder into the `htdocs` (XAMPP) or `www` (WAMP) directory.
3. If using the PHP built-in server, navigate to the folder in your terminal and run: `php -S localhost:8001`.

## 2. Database Setup
1. Open your database manager (e.g., phpMyAdmin or a terminal client).
2. Create a new database named `iot_security`.
3. Import the `database.sql` file provided in the root directory.
4. **Default Admin Credentials**:
   - **Username:** `admin`
   - **Password:** `admin123`

## 3. Web Configuration
1. Open `config/database.php` in a text editor.
2. Update the host, port, database name, username, and password to match your MySQL database configuration. 
   *(Note: The default is often `root` for the username and `root` or `blank` for the password depending on your setup)*.

## 4. Hardware Setup (ESP32)
1. Open the Arduino IDE.
2. Install the **ESP32** board package and the **ArduinoJson** library via the Library Manager.
3. Open `firmware/iot_security_node/iot_security_node.ino`.
4. Update the WiFi and API configuration at the top of the file:
   - `WIFI_SSID`: Your WiFi network name.
   - `WIFI_PASSWORD`: Your WiFi password.
   - `API_BASE_URL`: The URL to your PHP server (e.g., `http://192.168.1.100/iot_security_system/api/`).
5. Upload the code to your ESP32.

---

## 5. Setting up Email & SMS for Better Performance
For real-time intrusion detection, it is crucial to set up Email and SMS alerts. This ensures that you receive immediate notifications if a security breach occurs, avoiding the need to constantly monitor the dashboard.

### A. Email Setup (via SMTP)
To set up email alerts, you need an SMTP provider (like Gmail, SendGrid, or Mailtrap).
1. Log in to the web dashboard as `admin`.
2. Navigate to the **Settings** page.
3. Fill out the SMTP Configuration:
   - **SMTP Host:** `smtp.gmail.com` (for Gmail) or your provider's host.
   - **SMTP Port:** `587` (TLS) or `465` (SSL).
   - **SMTP Username:** Your email address (e.g., `your-email@gmail.com`).
   - **SMTP Password:** Your App Password (If using Gmail, do not use your regular password. Generate an "App Password" from your Google Account settings).
   - **Sender Email:** The email address sending the alerts.
   - **Recipient Email:** The email address where you want to receive security alerts.
4. Check **Enable Email Alerts** and click "Save Settings".

### B. SMS Setup (via Beam Africa API)
For immediate fallback when data/internet is unavailable on your mobile device, SMS provides the best performance and reliability.
1. Create an account with an SMS gateway provider like **Beam Africa** (or Twilio/Nexmo, depending on your integration).
2. Obtain your **API Token** from your provider's developer dashboard.
3. Log in to the web dashboard as `admin` and go to **Settings**.
4. Fill out the SMS Configuration:
   - **API URL:** `https://api.beamafrica.com/v1/send`
   - **API Token:** Paste your secret API token.
   - **Sender Name:** Provide an approved sender ID (e.g., `IOTSEC`).
   - **Recipient Phone:** Enter your mobile number in international format (e.g., `+1234567890`).
5. Check **Enable SMS Alerts** and click "Save Settings".

> **Performance Tip:** Ensure the web server hosting this project has a stable internet connection. Because API calls to SMTP and SMS providers block the execution thread until completed, a slow network might delay the dashboard's response when an alert is triggered. For production, consider moving the notification sending logic to a background queue or CRON job.
