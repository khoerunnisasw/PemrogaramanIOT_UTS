#include <WiFi.h>
#include <PubSubClient.h>
#include <DHT.h>

#define DHTPIN 15
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

const char* ssid     = "Wokwi-GUEST";
const char* password = "";

const char* mqtt_server = "broker.emqx.io"; // broker publik

WiFiClient espClient;
PubSubClient client(espClient);

void reconnect() {
  while (!client.connected()) {
    if (client.connect("ESP32SensorDHT22")) {
      // Connected
    } else {
      delay(1000);
    }
  }
}

void setup() {
  Serial.begin(115200);
  dht.begin();

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(300);
  }

  client.setServer(mqtt_server, 1883);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();

  float suhu = dht.readTemperature();   
  float humid = dht.readHumidity();     
  int light = analogRead(36);           // GPIO36 (A0)

  // Pastikan tidak NaN
  if (isnan(suhu) || isnan(humid)) {
    Serial.println("Failed to read from DHT22 sensor!");
    return;
  }

  // Publish ke MQTT
  client.publish("sensor/suhu", String(suhu).c_str());
  client.publish("sensor/humid", String(humid).c_str());
  client.publish("sensor/light", String(light).c_str());

  // Debug Serial
  Serial.print("Suhu: ");
  Serial.println(suhu);
  Serial.print("Humid: ");
  Serial.println(humid);
  Serial.print("Light: ");
  Serial.println(light);
  Serial.println("--------------------");

  delay(3000);
}
