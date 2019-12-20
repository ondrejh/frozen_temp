/**
 * based on BasicHTTPClient.ino
 * Created on: 20.12.2019
 *
 * Get sensor data and current time and display it on led panel
 */

#include <Arduino.h>

#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>

#include <ESP8266HTTPClient.h>

#define USE_SERIAL Serial

const char *ssid = "mobAp";
const char *password = "m0bAp123";

const char *url = "http://tomas-balicek.cz/brodak/core/load.php";

const String search_t1[3] = {"Temperature INPUT 1", "class=\"temperature\"", ">"};
const String search_t2[3] = {"Temperature INPUT 2", "class=\"temperature\"", ">"};
const String search_end = "<";

ESP8266WiFiMulti WiFiMulti;

void setup() {
  USE_SERIAL.begin(115200);
  USE_SERIAL.println("Start");
  USE_SERIAL.flush();
  delay(100);
  WiFiMulti.addAP(ssid, password);
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
  
  // wait for WiFi connection
  if((WiFiMulti.run() == WL_CONNECTED)) {
    if (first) {
      first = false;
      USE_SERIAL.print("[WIFI] IP address : ");
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

  delay(30000);
}
