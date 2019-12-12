Votuzilec sensor chart
======================

## Raspberry Pi installation:
```
  sudo apt update
  sudo apt upgrade

  sudo apt install apache2
  sudo chown -R pi:www-data /var/www/html/
  sudo chmod -R 770 /var/www/html/
  
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
- webi
- dokumentace
- jeste neco co jsem zapomnel ...
