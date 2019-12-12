Votuzilec sensor chart
======================

## Raspberry Pi installation:
Install software
```
  sudo apt update
  sudo apt upgrade

  sudo apt install apache2
  sudo chown -R pi:www-data /var/www/html/
  sudo chmod -R 770 /var/www/html/
  sudo apt install php
  sudo apt install mariadb-server php-mysql
```
Secure MySQL installation
```
  sudo mysql_secure_installation
```
Create MySQL user and privileges
```
  sudo mysql --user=root --password
  > create user admin@localhost identified my 'your_password';
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
Restart apache
```
  sudo service apache2 restart
```

  
  sudo apt install git
  git clone git@github.com:ondrejh/frozen_temp.git
  cd frozen_temp
  ./get_plotly.sh
  cp index.php /var/www/html/
  crontab -e
    <insert to the end:>
    */15 * * * * python3 /home/pi/get_data.py
```

## ToDo:
- přepsat do PHP a MySQL aby to šlo na webhosting
- dokumentace
- jeste neco co jsem zapomnel ...
- jo a screeshot by se hodil

## Sources:
LAMP installation: https://randomnerdtutorials.com/raspberry-pi-apache-mysql-php-lamp-server/
