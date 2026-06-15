# REST API Documentation

All API requests require a valid `api_key` sent in the JSON payload or query parameters.

### 1. Update Device Status
Used by the ESP32 to send periodic heartbeat and sensor states.

**Endpoint:** `POST /api/device/update.php`  
**Content-Type:** `application/json`

**Request Body:**
```json
{
  "api_key": "YOUR_API_KEY",
  "device_id": "ESP32_NODE_01",
  "pir": 0,
  "laser": 1,
  "ldr": 850
}
```

### 2. Send Alert
Used by the ESP32 when an intrusion is detected. Triggers SMS and Email notifications if enabled and system is Armed.

**Endpoint:** `POST /api/device/alert.php`  
**Content-Type:** `application/json`

**Request Body:**
```json
{
  "api_key": "YOUR_API_KEY",
  "device_id": "ESP32_NODE_01",
  "alert_type": "Motion Intrusion",
  "sensor": "PIR"
}
```

### 3. Get Status
Returns the current security mode (Armed/Disarmed) of the system.

**Endpoint:** `GET /api/device/status.php?api_key=YOUR_API_KEY&device_id=ESP32_NODE_01`

**Response:**
```json
{
  "status": "success",
  "mode": "Armed"
}
```

### 4. Get Configuration
Returns configuration thresholds for the hardware.

**Endpoint:** `GET /api/device/config.php?api_key=YOUR_API_KEY`

**Response:**
```json
{
  "status": "success",
  "ldr_threshold": 500,
  "heartbeat_interval": 30000
}
```
