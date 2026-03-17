#include <WiFi.h>
#include <PubSubClient.h>
#include <NTPClient.h>
#include <WiFiUdp.h>
#include <ArduinoJson.h>
#include <DHT.h>

// ===== Device Identity =====
const char* DEVICE_ID = "SMART_AGRICULTURE_001";

// ===== Initial WiFi (can be overwritten from MQTT wifi_config) =====
String wifiSsid = "YOUR_WIFI_SSID";
String wifiPassword = "YOUR_WIFI_PASSWORD";

// ===== MQTT Config =====
const char* MQTT_HOST = "broker.hivemq.com";
const uint16_t MQTT_PORT = 1883;

// ===== Pin Config =====
constexpr uint8_t SOIL_PIN = 34;
constexpr uint8_t RELAY_PIN = 26;
constexpr uint8_t DHT_PIN = 27;
constexpr uint8_t DHT_TYPE = DHT22;

// ===== Watering Rules =====
constexpr int SOIL_THRESHOLD_DRY = 35;      // below = dry
constexpr float TEMP_THRESHOLD_HOT = 32.0;  // above = hot
constexpr uint32_t PUBLISH_INTERVAL_MS = 5000;  // rate limiting 1/5s
constexpr uint32_t HEARTBEAT_INTERVAL_MS = 60000;
constexpr uint32_t MAX_PUMP_ON_MS = 60000;      // fail-safe 1 minute

WiFiClient wifiClient;
PubSubClient mqttClient(wifiClient);
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", 7 * 3600, 60000); // WIB UTC+7
DHT dht(DHT_PIN, DHT_TYPE);

unsigned long lastPublishMs = 0;
unsigned long lastHeartbeatMs = 0;
unsigned long pumpStartedMs = 0;
bool pumpActive = false;
String manualSchedule = "07:00";
bool scheduleEnabled = true;

String topicBase() { return "devices/" + String(DEVICE_ID); }

bool isBestWateringTime() {
  timeClient.update();
  int currentHour = timeClient.getHours();
  return (currentHour >= 5 && currentHour <= 8) || (currentHour >= 16 && currentHour <= 18);
}

int readSoilPercent() {
  int raw = analogRead(SOIL_PIN);
  int pct = map(raw, 4095, 1200, 0, 100);
  return constrain(pct, 0, 100);
}

void setPump(bool on) {
  digitalWrite(RELAY_PIN, on ? LOW : HIGH); // LOW active relay
  if (on && !pumpActive) {
    pumpStartedMs = millis();
  }
  pumpActive = on;
}

void publishJson(const String& topic, JsonDocument& doc) {
  char buffer[512];
  size_t n = serializeJson(doc, buffer);
  mqttClient.publish(topic.c_str(), buffer, n);
}

void publishHeartbeat() {
  StaticJsonDocument<192> doc;
  doc["device_id"] = DEVICE_ID;
  doc["status"] = "online";
  doc["last_seen"] = timeClient.getEpochTime();
  publishJson(topicBase() + "/heartbeat", doc);
}

void publishSensorData() {
  if (millis() - lastPublishMs < PUBLISH_INTERVAL_MS) return;

  float temp = dht.readTemperature();
  float hum = dht.readHumidity();
  int soil = readSoilPercent();

  StaticJsonDocument<256> doc;
  doc["device_id"] = DEVICE_ID;
  doc["timestamp"] = timeClient.getEpochTime();
  doc["sensor_data"]["temperature_c"] = isnan(temp) ? -1 : temp;
  doc["sensor_data"]["humidity_pct"] = isnan(hum) ? -1 : hum;
  doc["sensor_data"]["soil_moisture_pct"] = soil;
  doc["control_status"]["pump"] = pumpActive ? "on" : "off";

  publishJson(topicBase() + "/sensor_data", doc);
  lastPublishMs = millis();
}

void publishHistory(const char* mode, int soilBefore, int soilAfter, int durationSec) {
  StaticJsonDocument<256> doc;
  doc["mode"] = mode;
  doc["started_at"] = timeClient.getEpochTime();
  doc["duration_sec"] = durationSec;
  doc["soil_before"] = soilBefore;
  doc["soil_after"] = soilAfter;
  publishJson(topicBase() + "/history", doc);
}

void processAutoLogic() {
  int soil = readSoilPercent();
  float temp = dht.readTemperature();

  bool shouldWater = soil < SOIL_THRESHOLD_DRY && isBestWateringTime();
  if (!isnan(temp) && temp > TEMP_THRESHOLD_HOT && soil < 45) {
    shouldWater = true;
  }

  if (scheduleEnabled) {
    timeClient.update();
    char hhmm[6];
    snprintf(hhmm, sizeof(hhmm), "%02d:%02d", timeClient.getHours(), timeClient.getMinutes());
    if (manualSchedule == String(hhmm)) {
      shouldWater = true;
    }
  }

  if (shouldWater && !pumpActive) {
    int soilBefore = soil;
    setPump(true);
    delay(10000); // watering chunk
    setPump(false);
    publishHistory("auto", soilBefore, readSoilPercent(), 10);
  }
}

void mqttCallback(char* topic, byte* payload, unsigned int len) {
  String topicStr(topic);
  String body;
  for (unsigned int i = 0; i < len; i++) body += (char)payload[i];

  StaticJsonDocument<256> doc;
  if (deserializeJson(doc, body)) return;

  if (topicStr.endsWith("/schedule")) {
    if (doc.containsKey("time")) manualSchedule = String((const char*)doc["time"]);
    if (doc.containsKey("enabled")) scheduleEnabled = doc["enabled"].as<bool>();
  }

  if (topicStr.endsWith("/manual_trigger")) {
    bool active = doc["active"] | false;
    int duration = constrain(doc["duration_sec"] | 10, 1, 60);
    if (active) {
      int soilBefore = readSoilPercent();
      setPump(true);
      delay(duration * 1000);
      setPump(false);
      publishHistory("manual", soilBefore, readSoilPercent(), duration);
    }
  }

  if (topicStr.endsWith("/wifi_config")) {
    String newSsid = String((const char*)doc["ssid"]);
    String newPass = String((const char*)doc["password"]);
    if (newSsid.length() > 0) {
      wifiSsid = newSsid;
      wifiPassword = newPass;
      WiFi.disconnect();
    }
  }
}

void connectWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(wifiSsid.c_str(), wifiPassword.c_str());
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
}

void connectMQTT() {
  mqttClient.setServer(MQTT_HOST, MQTT_PORT);
  mqttClient.setCallback(mqttCallback);

  while (!mqttClient.connected()) {
    String clientId = "ESP32-" + String(DEVICE_ID);
    if (mqttClient.connect(clientId.c_str())) {
      mqttClient.subscribe((topicBase() + "/schedule").c_str());
      mqttClient.subscribe((topicBase() + "/manual_trigger").c_str());
      mqttClient.subscribe((topicBase() + "/wifi_config").c_str());
    } else {
      delay(1500);
    }
  }
}

void ensureConnection() {
  if (WiFi.status() != WL_CONNECTED) connectWiFi();
  if (!mqttClient.connected()) connectMQTT();
}

void setup() {
  pinMode(RELAY_PIN, OUTPUT);
  setPump(false);
  Serial.begin(115200);
  dht.begin();
  connectWiFi();
  timeClient.begin();
  connectMQTT();
}

void loop() {
  ensureConnection();
  mqttClient.loop();
  timeClient.update();

  if (pumpActive && millis() - pumpStartedMs >= MAX_PUMP_ON_MS) {
    setPump(false); // fail-safe shutdown
  }

  processAutoLogic();
  publishSensorData();

  if (millis() - lastHeartbeatMs >= HEARTBEAT_INTERVAL_MS) {
    publishHeartbeat();
    lastHeartbeatMs = millis();
  }

  delay(100);
}
