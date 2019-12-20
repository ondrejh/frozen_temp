/**
 * based on BasicHTTPClient.ino
 * Created on: 20.12.2019
 *
 * Get sensor data and current time and display it on led panel
 */

#include <Arduino.h>

// http client
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>

// includes (ntp client)
#include <WiFiUdp.h>
#include <NTPClient.h>

#define USE_SERIAL Serial

const char *ssid = "mobAp";
const char *password = "m0bAp123";

const char *url = "http://tomas-balicek.cz/brodak/core/load.php";

const String search_t1[3] = {"Temperature INPUT 1", "class=\"temperature\"", ">"};
const String search_t2[3] = {"Temperature INPUT 2", "class=\"temperature\"", ">"};
const String search_end = "<";

const uint32_t updateTime_ms = 30000;

ESP8266WiFiMulti WiFiMulti;

// ntp client
const long utcOffsetInSeconds = 3600; // SELC
const long ntpUpdateInterval = 3600000; // 1h
const char *ntpServer = "tik.cesnet.cz";
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, ntpServer, utcOffsetInSeconds, ntpUpdateInterval);

void setup() {
  USE_SERIAL.begin(115200);
  USE_SERIAL.println("Start");
  USE_SERIAL.flush();
  delay(100);
  // start wifi
  WiFiMulti.addAP(ssid, password);
  // start ncpt client
  timeClient.begin();
}

String find_in_string(String str, const String *sb, int nsb, const String sa) {
  int pos = 0;
  for (int i=0; i<nsb; i++) {
    int index = str.substring(pos).indexOf(sb[i]);
    if (index<0)
      return("");
    else
      pos += index + sb[i].length();
  }
  int index = str.substring(pos).indexOf(sa);
  if (index>0)
    return(str.substring(pos, pos+index));
  return("");
}

void loop() {
  static bool first = true;

  uint32_t now = millis();
  static uint32_t tick = millis();

  // upgrade internet data
  if ((int32_t)(tick - now) <= 0) {
    tick = now + updateTime_ms;
    
    // wait for WiFi connection
    if((WiFiMulti.run() == WL_CONNECTED)) {
      if (first) {
        first = false;
        USE_SERIAL.print("ip: ");
        USE_SERIAL.print(WiFi.localIP());
        USE_SERIAL.println();
      }
      
      HTTPClient http;
      http.begin(url); //HTTP
      int httpCode = http.GET();
  
      // httpCode will be negative on error
      if(httpCode > 0) {
        // file found at server
        if(httpCode == HTTP_CODE_OK) {
          String payload = http.getString();
          //USE_SERIAL.println(payload);
          String i1 = find_in_string(payload, search_t1, 3, search_end);
          USE_SERIAL.print("T1: ");
          USE_SERIAL.print(i1);
          USE_SERIAL.print("°C");
          String i2 = find_in_string(payload, search_t2, 3, search_end);
          USE_SERIAL.print("  T2: ");
          USE_SERIAL.print(i2);
          USE_SERIAL.print("°C");
          USE_SERIAL.println();
        }
      } else {
        USE_SERIAL.printf("http get failed (%s)\n", http.errorToString(httpCode).c_str());
      }
  
      http.end();
    }
    else
      first = true;
  
    timeClient.update();
    USE_SERIAL.print(timeClient.getFormattedTime());
    USE_SERIAL.println();
  }

  
}
