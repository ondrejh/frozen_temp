Votuzilec sensor chart
======================

## Plot screenshots

Measurement chart:

![Measurement chart](/doc/measurement_plot.png)

Statistics chart:

![Statistics chart](/doc/statistics_plot.png)

## Raspberry Pi installation (python, sqlite - lite version)
Install software (apache, php, git)
```
  sudo apt update
  sudo apt upgrade

  sudo apt install apache2
  sudo chown -R pi:www-data /var/www/html/
  sudo chmod -R 770 /var/www/html/
  sudo apt install php

  sudo apt install git
```
Get project files from git repository
```
  git clone https://github.com/ondrejh/frozen_temp.git
  cd frozen_temp
  ./get_plotly.sh
```
Copy page files
```
  cp ploly.min.js /var/www/html/
  cp index.php /var/www/html/
```
Start cron to get data every 15 minutes and last day statistics every day at 00:20
```
  crontab -e
    .. navigate to the end of file and type
    */15 * * * * python3 /home/pi/get_data.py
    20 0 * * * python3 /home/pi/get_data.py -s
    .. save
```
Restart apache
```
  sudo service apache2 restart
```

## MySQL and PHP version instalation
Secure MySQL installation
```
  sudo mysql_secure_installation
  sudo apt install mariadb-server php-mysql
```
Create MySQL user and privileges
```
  sudo mysql --user=root --password
  > create user admin@localhost identified my '1243';
  > grant all privileges on *.* to admin@localhost;
  > FLUSH PRIVILEGES;
  > exit;
```
Create database
```
  mysql --user=admin --password
  > CREATE DATABASE votuzilec;
  > exit;
```
Copy files
```
  cp plotly.min.js /var/www/html/
  cp index.php get_data.php /var/www/html
```
Start cron to get data every 15 minutes
```
  crontab -e
    .. navigate to the end of file and type
    */15 * * * * php /var/www/html/get_data.php
    20 0 * * * php /var/www/html/statistics.php
    .. save
```
Restart apache
```
  sudo service apache2 restart
```

## ToDo:
- sqlite version using php
- hw display

## Sources:
LAMP installation: https://randomnerdtutorials.com/raspberry-pi-apache-mysql-php-lamp-server/
