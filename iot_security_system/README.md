# IoT Security System

A comprehensive, real-time IoT monitoring and security platform featuring Beem Africa SMS integration, advanced false-positive reduction algorithms, and a premium dark-mode dashboard.

## Features

- **Multi-Device Support:** Manage an unlimited number of ESP32 sensor nodes.
- **Real-Time Dashboard:** Live polling for device status, alert charts, and detection statistics.
- **Beem Africa SMS Integration:** Native integration with Beem Africa's API for reliable alert delivery, including balance checking and test sending.
- **Advanced False-Positive Reduction:**
  - Configurable PIR sensitivity and detection cooldowns.
  - Motion confirmation counters.
  - Confidence threshold filtering.
  - Granular alert trigger modes (e.g., Critical Only, Both Sensors).
- **Custom SMS Templates:** Dynamic variable substitution (`{device_name}`, `{alert_type}`, etc.) for custom alerts.
- **Complete Audit Trail:** Track user logins, settings changes, and device activity.
- **Premium UI:** Glassmorphism design, native dark/light mode, responsive layouts, and Chart.js analytics.

## Tech Stack

- **Backend:** PHP 8, PDO, MySQL/MariaDB
- **Frontend:** HTML5, CSS3 (Vanilla + Custom Variables), Vanilla JS, Chart.js, Font Awesome 6
- **Firmware:** C++ (Arduino Core for ESP32)

## Setup and Installation

See `INSTALL.md` for complete installation and configuration instructions.

## API Documentation

See `API.md` for details on the communication protocol between the ESP32 nodes and the PHP backend.

## Architecture

The system operates on a hub-and-spoke model. The PHP backend acts as the central hub, receiving HTTP POST requests (heartbeats and alerts) from edge nodes. The backend evaluates the data against the user's configured rules in real-time, determines if an alert meets the confidence threshold, and triggers the `NotificationEngine` to dispatch SMS via Beem Africa.
