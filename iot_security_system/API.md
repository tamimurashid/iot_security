# IoT Security System API Documentation

All API endpoints expect and return JSON. Authentication is handled via the `api_key` parameter (either in the JSON body or as a query parameter).

## Endpoints

### 1. Heartbeat / Status Update (`POST /api/device/update.php`)
Sent by the device periodically to maintain "Online" status and sync basic state. If the `device_id` is unknown, the system will automatically register it.

**Request:**
```json
{
    "api_key": "YOUR_API_KEY",
    "device_id": "ESP32_NODE_01",
    "firmware": "1.0.2",
    "pir": 0,
    "laser": 1,
    "ldr": 450
}
```

### 2. Trigger Alert (`POST /api/device/alert.php`)
Sent by the device when an intrusion is detected. The server evaluates this against user-defined thresholds (cooldowns, confidence).

**Request:**
```json
{
    "api_key": "YOUR_API_KEY",
    "device_id": "ESP32_NODE_01",
    "alert_type": "Motion Intrusion",
    "sensor": "PIR",
    "confidence": 95
}
```

### 3. Fetch Configuration (`GET /api/device/config.php`)
Fetched by the device on boot or periodically to sync detection and buzzer settings.

**Request:**
`GET /api/device/config.php?api_key=YOUR_API_KEY`

**Response:**
```json
{
    "status": "success",
    "buzzer_mode_pir": "beep",
    "buzzer_mode_laser": "continuous",
    "buzzer_duration": 2000,
    "pir_sensitivity": "medium",
    "detection_cooldown": 30,
    "motion_confirm_count": 1
}
```

### 4. Device Registration (`POST /api/device/register.php`)
Optional endpoint for explicit device registration before sending heartbeats.

**Request:**
```json
{
    "api_key": "YOUR_API_KEY",
    "device_id": "ESP32_NODE_02",
    "device_name": "Garage Sensor",
    "location": "North Wall",
    "firmware_version": "1.0.0"
}
```
