<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
$db_host = 'localhost';
$db_username = 'admin';
$db_password = '1234';

$db_name = 'votuzilec';
$db_table = 'teplomer';

$db_stamp = 'stamp';

$db_stat = 'statistika';
$db_t1 = 't1';
$db_t2 = 't2';
$db_avg = 'avg';
$db_max = 'max';
$db_min = 'min';

$db_conn = new mysqli($db_host, $db_username, $db_password, $db_name);

// create table if not exist
$query = "CREATE TABLE IF NOT EXISTS ". $db_stat. " (id INT AUTO_INCREMENT PRIMARY KEY, ". $db_t1. $db_max. " FLOAT, ".
         $db_t1. $db_avg. " FLOAT, ". $db_t1. $db_min. " FLOAT, ". $db_t2. $db_max. " FLOAT, ". $db_t2. $db_avg. " FLOAT, ".
         $db_t2. $db_min. " FLOAT, ".$db_stamp. " TIMESTAMP);";
$db_data = $db_conn->query($query);

// get from date (od (from))
$query = "SELECT MAX(". $db_stamp. ") FROM ". $db_stat. ";";
$db_data = $db_conn->query($query);
$from_date = $db_data->fetch_all()[0][0];
if ($from_date == NULL) { // can't get 'from_date' from statistics - use data table min
    $query = "SELECT MIN(". $db_stamp. ") FROM ". $db_table. ";";
    $db_data = $db_conn->query($query);
    $from_date = $db_data->fetch_all()[0][0];
    $from_date = date("Y-m-d", strtotime($from_date));
}
else {
    $from_date = date("Y-m-d", strtotime('+1 day', strtotime($from_date)));
}
#echo 'od '. $from_date. PHP_EOL;

// get last date (do (to))
$query = "SELECT MAX(". $db_stamp. ") FROM ". $db_table. ";";
$db_data = $db_conn->query($query);
$to_date = date("Y-m-d", strtotime($db_data->fetch_all()[0][0]));
#echo 'do '. $to_date. PHP_EOL;
    
// from to cycle
$from_dt = new DateTime($from_date);
$to_dt = new DateTime($to_date);
$interval = DateInterval::createFromDateString('1 day');
$period = new DatePeriod($from_dt, $interval, $to_dt);

$added_cnt = 0;
foreach ($period as $dt) {

    $dt_str = $dt->format("Y-m-d");
    $query = "SELECT * FROM ". $db_table. " WHERE DATE(". $db_stamp. ") = '". $dt_str. "';";
    #echo $query. PHP_EOL;
    if ($db_data = $db_conn->query($query)) {
        $t1avg = NULL;
        $t1min = 0;
        $t1max = 0;
        $t2avg = 0;
        $t2min = 0;
        $t2max = 0;
        $tcnt = 0;

        while ($row = $db_data->fetch_row()) {
            #var_dump($row);
            if ($t1avg == NULL) {
                $t1avg = $row[1];
                $t1min = $row[1];
                $t1max = $row[1];
                $t2avg = $row[2];
                $t2min = $row[2];
                $t2max = $row[2];
            }
            else {
                $t1avg += $row[1];
                if ($t1min > $row[1])
                    $t1min = $row[1];
                if ($t1max < $row[1])
                    $t1max = $row[1];
                $t2avg += $row[2];
                if ($t2min > $row[2])
                    $t2min = $row[2];
                if ($t2max < $row[2])
                    $t2max = $row[2];
            }
            $tcnt ++;
        }
        if ($tcnt > 0) {
            $t1avg /= $tcnt;
            $t1avg = number_format($t1avg,2,'.','');
            $t2avg /= $tcnt;
            $t2avg = number_format($t2avg,2,'.','');
            $query = "INSERT INTO ". $db_stat. "(t1min, t1avg, t1max, t2min, t2avg, t2max, stamp) VALUES (". $t1min. ", ". $t1avg. ", ". $t1max .", ". $t2min. ", ". $t2avg. ", ". $t2max. ", '". $dt_str. "');";
            $db_conn->query($query);
            $added_cnt ++;
            #echo $query. PHP_EOL;
            echo 't1: '. $t1min. ', '. $t1avg. ', '. $t1max. ' t2: '. $t2min. ', '. $t2avg. ', '. $t2max. PHP_EOL;
        }
    }   
}
echo "Added ". $added_cnt. " rows.". PHP_EOL;

$db_conn->commit();
$db_conn->close();

?>