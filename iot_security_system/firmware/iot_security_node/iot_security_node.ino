#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <WiFiManager.h> // Include WiFiManager library

// --- Configuration ---
// WiFi credentials are now managed by WiFiManager


String API_BASE_URL = "http://192.168.1.4:8000/api/";
String API_KEY = "EggrollKey123";
String DEVICE_ID = "ESP32_NODE_01";

// --- Pin Definitions ---
const int PIR_PIN = 18;
const int LASER_PIN = 26; // Providing power to the laser
const int LDR_PIN = 34;
const int BUZZER_PIN = 5;

// --- Thresholds & Timers ---
const int LDR_THRESHOLD_LOW = 800;  // Laser broken if below this
const int LDR_THRESHOLD_HIGH = 1000; // Laser okay if above this
unsigned long lastHeartbeat = 0;
const unsigned long HEARTBEAT_INTERVAL = 30000; // 30 seconds

// --- Dynamic Config ---
String buzzerModePir = "beep";
String buzzerModeLaser = "continuous";
int buzzerDuration = 2000;

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
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);
  
  // Turn on Laser
  digitalWrite(LASER_PIN, HIGH);
  
  connectWiFi();
  fetchConfig();
}

void loop() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi connection lost. Reconnecting...");
    WiFi.reconnect();
    unsigned long startAttemptTime = millis();
    while (WiFi.status() != WL_CONNECTED && millis() - startAttemptTime < 5000) {
      delay(500);
      Serial.print(".");
    }
    Serial.println();
  }
  
  int pirValue = digitalRead(PIR_PIN);
  int ldrValue = analogRead(LDR_PIN);
  
  // Check PIR Sensor
  if (pirValue == HIGH) {
    if (lastPirState == LOW) {
      Serial.println("Motion detected!");
      sendAlert("Motion Intrusion", "PIR");
      triggerBuzzer(buzzerModePir);
      lastPirState = HIGH;
    }
  } else {
    if (lastPirState == HIGH) {
      lastPirState = LOW;
    }
  }
  
  // Check Laser (Beam Break)
  // If LDR value drops below threshold, beam is broken
  if (ldrValue < LDR_THRESHOLD_LOW) {
    if (!lastLaserBroken) {
      Serial.println("Laser beam broken!");
      sendAlert("Beam Break Intrusion", "Laser");
      triggerBuzzer(buzzerModeLaser);
      lastLaserBroken = true;
    }
  } else if (ldrValue > LDR_THRESHOLD_HIGH) {
    if (lastLaserBroken) {
      lastLaserBroken = false;
    }
  }
  
  // Send Heartbeat (Update)
  if (millis() - lastHeartbeat >= HEARTBEAT_INTERVAL) {
    sendHeartbeat(pirValue, lastLaserBroken ? 1 : 0, ldrValue);
    fetchConfig(); // Update config periodically
    lastHeartbeat = millis();
  }
  
  delay(100);
}

void connectWiFi() {
  Serial.println("Starting WiFiManager...");
  WiFiManager wm;
  
  // Set a timeout so it doesn't block forever if there is no setup
  wm.setConfigPortalTimeout(180);
  
  // Auto connect or start the access point for configuration
  if (!wm.autoConnect("ESP32_Security_Node")) {
    Serial.println("Failed to connect and hit timeout. Restarting...");
    delay(3000);
    ESP.restart();
    delay(5000);
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
      if (httpResponseCode != 200 && httpResponseCode != 201) {
        Serial.print("Server response: ");
        Serial.println(http.getString());
      }
    } else {
      Serial.print("Error sending alert: ");
      Serial.println(http.errorToString(httpResponseCode).c_str());
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
      if (httpResponseCode != 200 && httpResponseCode != 201) {
        Serial.print("Server response: ");
        Serial.println(http.getString());
      }
    } else {
      Serial.print("Error sending heartbeat: ");
      Serial.println(http.errorToString(httpResponseCode).c_str());
    }
    
    http.end();
  }
}

void fetchConfig() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(API_BASE_URL + "device/config.php?api_key=" + API_KEY);
    int httpResponseCode = http.GET();
    if (httpResponseCode > 0) {
      String payload = http.getString();
      StaticJsonDocument<512> doc;
      DeserializationError error = deserializeJson(doc, payload);
      if (!error) {
        if (doc.containsKey("buzzer_mode_pir")) buzzerModePir = doc["buzzer_mode_pir"].as<String>();
        if (doc.containsKey("buzzer_mode_laser")) buzzerModeLaser = doc["buzzer_mode_laser"].as<String>();
        if (doc.containsKey("buzzer_duration")) buzzerDuration = doc["buzzer_duration"].as<int>();
        Serial.println("Config updated from server.");
      }
    }
    http.end();
  }
}

void triggerBuzzer(String mode) {
  if (mode == "silent") {
    // do nothing
  } else if (mode == "once") {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(buzzerDuration);
    digitalWrite(BUZZER_PIN, LOW);
  } else if (mode == "beep") {
    int beepTime = 100;
    int numBeeps = buzzerDuration / (beepTime * 2);
    if (numBeeps == 0) numBeeps = 1;
    for (int i = 0; i < numBeeps; i++) {
      digitalWrite(BUZZER_PIN, HIGH);
      delay(beepTime);
      digitalWrite(BUZZER_PIN, LOW);
      delay(beepTime);
    }
  } else {
    // continuous or unknown defaults to continuous
    digitalWrite(BUZZER_PIN, HIGH);
    delay(buzzerDuration);
    digitalWrite(BUZZER_PIN, LOW);
  }
}
