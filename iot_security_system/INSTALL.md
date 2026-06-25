# Installation Guide

## 1. Prerequisites
- PHP 8.0 or higher
- MySQL / MariaDB (e.g., via MAMP, XAMPP, or natively)
- Web Server (Apache, Nginx, or PHP Built-in Server)

## 2. Database Setup

1. Open your MySQL client (e.g., phpMyAdmin or terminal).
2. Create the database and import the schema:
   ```bash
   mysql -u root -p < database.sql
   ```
   *Note: The `database.sql` script uses `IF NOT EXISTS` and `ON DUPLICATE KEY`, so it is safe to run against an existing installation without losing device or settings data.*

3. Configure Database Connection:
   Open `config/database.php` and update the credentials if necessary:
   ```php
   $host = '127.0.0.1'; // Or localhost
   $db   = 'iot_security';
   $user = 'root';
   $pass = 'root';      // MAMP default is usually 'root'
   ```

## 3. Web Server Setup

### Using PHP Built-in Server (Development)
Open a terminal in the root folder (`iot_security_system`) and run:
```bash
php -S 0.0.0.0:8000
```
Then visit `http://localhost:8000` in your browser.

### Using MAMP / XAMPP
Copy the entire `iot_security_system` folder into your `htdocs` (MAMP/XAMPP) directory and access it via `http://localhost/iot_security_system`.

## 4. Default Credentials

- **URL:** `http://localhost:8000/index.php`
- **Username:** `admin`
- **Password:** `admin123`

## 5. Beem Africa SMS Configuration

1. Log into the system using the default credentials.
2. Navigate to **Settings** -> **SMS (Beem Africa)** tab.
3. Toggle "Enable SMS" to ON.
4. Enter your Beem Africa **API Key** and **Secret Key**.
5. Set your **Sender Name** (must be approved by Beem Africa).
6. Add your **Recipient Phone Number** (e.g., `255712345678`).
7. Click **Save All Settings**.
8. Use the **Send Test SMS** button to verify your configuration.

## 6. Device Configuration

1. Navigate to **Devices** and click **Add Device**.
2. Enter an ID (e.g., `ESP32_NODE_01`) and a Name (e.g., `Front Door`).
3. Flash your ESP32 with the firmware in `firmware/iot_security_node`.
4. Ensure the `apiUrl` in `iot_security_node.ino` matches your server's IP address.
