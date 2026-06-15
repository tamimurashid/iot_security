#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// --- Configuration ---
const char* WIFI_SSID = "YOUR_WIFI_SSID";
const char* WIFI_PASSWORD = "YOUR_WIFI_PASSWORD";

String API_BASE_URL = "http://your-server-ip/iot_security_system/api/";
String API_KEY = "YOUR_API_KEY";
String DEVICE_ID = "ESP32_NODE_01";

// --- Pin Definitions ---
const int PIR_PIN = 14;
const int LASER_PIN = 26; // Providing power to the laser
const int LDR_PIN = 34;

// --- Thresholds & Timers ---
const int LDR_THRESHOLD = 500; // Adjust based on ambient light and laser intensity
unsigned long lastHeartbeat = 0;
const unsigned long HEARTBEAT_INTERVAL = 30000; // 30 seconds

// --- State Variables ---
int pirState = LOW;
int lastPirState = LOW;
bool laserBroken = false;
bool lastLaserBroken = false;

void setup() {
  Serial.begin(115200);
  
  pinMode(PIR_PIN, INPUT);
  pinMode(LASER_PIN, OUTPUT);
  pinMode(LDR_PIN, INPUT);
  
  // Turn on Laser
  digitalWrite(LASER_PIN, HIGH);
  
  connectWiFi();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    connectWiFi();
  }
  
  int pirValue = digitalRead(PIR_PIN);
  int ldrValue = analogRead(LDR_PIN);
  
  // Check PIR Sensor
  if (pirValue == HIGH) {
    if (lastPirState == LOW) {
      Serial.println("Motion detected!");
      sendAlert("Motion Intrusion", "PIR");
      lastPirState = HIGH;
    }
  } else {
    if (lastPirState == HIGH) {
      lastPirState = LOW;
    }
  }
  
  // Check Laser (Beam Break)
  // If LDR value drops below threshold, beam is broken
  if (ldrValue < LDR_THRESHOLD) {
    if (!lastLaserBroken) {
      Serial.println("Laser beam broken!");
      sendAlert("Beam Break Intrusion", "Laser");
      lastLaserBroken = true;
    }
  } else {
    if (lastLaserBroken) {
      lastLaserBroken = false;
    }
  }
  
  // Send Heartbeat (Update)
  if (millis() - lastHeartbeat >= HEARTBEAT_INTERVAL) {
    sendHeartbeat(pirValue, lastLaserBroken ? 1 : 0, ldrValue);
    lastHeartbeat = millis();
  }
  
  delay(100);
}

void connectWiFi() {
  Serial.print("Connecting to WiFi: ");
  Serial.println(WIFI_SSID);
  
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
  
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  
  Serial.println("\nWiFi Connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

void sendAlert(String alertType, String sensorSource) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(API_BASE_URL + "device/alert.php");
    http.addHeader("Content-Type", "application/json");
    
    StaticJsonDocument<200> doc;
    doc["api_key"] = API_KEY;
    doc["device_id"] = DEVICE_ID;
    doc["alert_type"] = alertType;
    doc["sensor"] = sensorSource;
    
    String requestBody;
    serializeJson(doc, requestBody);
    
    int httpResponseCode = http.POST(requestBody);
    
    if (httpResponseCode > 0) {
      Serial.print("Alert Sent. Response Code: ");
      Serial.println(httpResponseCode);
    } else {
      Serial.print("Error sending alert: ");
      Serial.println(httpResponseCode);
    }
    
    http.end();
  }
}

void sendHeartbeat(int pir, int laser, int ldr) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(API_BASE_URL + "device/update.php");
    http.addHeader("Content-Type", "application/json");
    
    StaticJsonDocument<200> doc;
    doc["api_key"] = API_KEY;
    doc["device_id"] = DEVICE_ID;
    doc["pir"] = pir;
    doc["laser"] = laser;
    doc["ldr"] = ldr;
    
    String requestBody;
    serializeJson(doc, requestBody);
    
    int httpResponseCode = http.POST(requestBody);
    
    if (httpResponseCode > 0) {
      Serial.println("Heartbeat sent successfully.");
    } else {
      Serial.print("Error sending heartbeat: ");
      Serial.println(httpResponseCode);
    }
    
    http.end();
  }
}
