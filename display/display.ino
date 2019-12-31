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

#define MAX_ERRS 3

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

void get_temp(String str, float *t, int *errs, bool *rdy) {
  if (str == "") {
    if (*rdy) {
      if (*errs > MAX_ERRS)
        *rdy = false;
      else
        *errs++;
    }   
  }
  else {
    *t = str.toFloat();
    *rdy = true;
    *errs = 0;
  }
}

void loop() {
  static bool first = true;

  static bool ntp_synchronized = false;
  static bool t1_ready = false, t2_ready = false;
  static int t1_errs = 0, t2_errs = 0;
  static float t1 = 0.0, t2 = 0.0;

  uint32_t now = millis();
  static uint32_t tick = now;

  // upgrade internet data
  if ((int32_t)(tick - now) <= 0) {
    tick = now + updateTime_ms;
    
    String i1 = "", i2 = "";
  
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
          i1 = find_in_string(payload, search_t1, 3, search_end);
          i2 = find_in_string(payload, search_t2, 3, search_end);
        }
      } else {
        USE_SERIAL.printf("http get failed (%s)\n", http.errorToString(httpCode).c_str());
      }
  
      http.end();
      
    }
    else
      first = true;

    get_temp(i1, &t1, &t1_errs, &t1_ready);
    get_temp(i2, &t2, &t2_errs, &t2_ready);

    if ((t1_ready) && (t2_ready)) {
      USE_SERIAL.print("T1: ");
      USE_SERIAL.print(t1);
      USE_SERIAL.print("°C"  );
      USE_SERIAL.print("  T2: ");
      USE_SERIAL.print(t2);
      USE_SERIAL.print("°C");
      USE_SERIAL.println();
    }

    timeClient.update();
    if (ntp_synchronized) {
      USE_SERIAL.print(timeClient.getFormattedTime());
      USE_SERIAL.println();
    }
    else {
      // stupid way how to find if its synchronized
      if (timeClient.getEpochTime() > 0x1234)
        ntp_synchronized = true;
    }
  }
}
