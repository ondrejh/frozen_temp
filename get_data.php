<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
    
$db_host = 'localhost';
$db_username = 'admin';
$db_password = '1234';

$db_name = 'votuzilec';

$db_table = 'teplomer';
$db_t1 = 't1';
$db_t2 = 't2';
$db_stamp = 'stamp';

$db_stat_table = 'statistika';
$db_min = 'min';
$db_avg = 'avg';
$db_max = 'max';

$sqlite_name = '/var/www/html/data.sql';


// return part of string between 'before' (array) and 'after' strings
function find($where, $before, $after) {
  $b = 0;
  for ($i = 0; $i < count($before); $i++)
    $b += strpos(substr($where, $b), $before[$i]) + strlen($before[$i]);
  $e = strpos(substr($where, $b), $after);
  return substr($where, $b, $e);
}


// get data from sensors (by parsing web page)
function get_sensors() {
  $fc = file_get_contents("http://89.190.88.10:89/status.html");

  $t1 = find($fc, ['Temperature INPUT 1', 'class="temperature"', '>'], '<');
  $t2 = find($fc, ['Temperature INPUT 2', 'class="temperature"', '>'], '<');

  return [$t1, $t2];
}

// insert data into database
function insert_sql($t1, $t2) {
  global $db_host, $db_username, $db_password, $db_name, $db_table, $db_t1, $db_t2, $db_stamp;

  $db_conn = new mysqli($db_host, $db_username, $db_password, $db_name);

  $query = "CREATE TABLE IF NOT EXISTS ". $db_table. "(id INT AUTO_INCREMENT PRIMARY KEY, ". $db_t1. " FLOAT, ".
         $db_t2. " FLOAT, ". $db_stamp. " TIMESTAMP DEFAULT CURRENT_TIMESTAMP)";
  $db_conn->query($query);

  $query = "INSERT INTO ". $db_table. " (". $db_t1. ", ". $db_t2. ") VALUES (". $t1. ", ". $t2. ")";
  $db_conn->query($query);

  $db_conn->close();
}


// get data from sql database
function get_sql($limit = 'ALL') {
  global $db_host, $db_username, $db_password, $db_name, $db_table, $db_t1, $db_t2, $db_stamp;

  $db_conn = new mysqli($db_host, $db_username, $db_password, $db_name);
  $query = "SELECT ". $db_stamp. ", ". $db_t1. ", ". $db_t2. " FROM ". $db_table;
  if ($limit != 'ALL')
    $query = $query. " WHERE ". $db_stamp. " >= NOW() - INTERVAL ". $limit;
  $query = $query. " ORDER BY ". $db_stamp;
  //echo $query. PHP_EOL;
  $db_data = $db_conn->query($query);
  $data = array(); 
  while($row = $db_data->fetch_array()) {
    $data[] = array(date("Y-m-d H:i", strtotime($row[$db_stamp])), $row[$db_t1], $row[$db_t2]);
  }
  $db_conn->close();
  return $data;
}


// get statistics data from sql database
function get_sql_statistics() {
  global $db_host, $db_username, $db_password, $db_name, $db_stat_table, $db_t1, $db_t2, $db_min, $db_avg, $db_max, $db_stamp;

  $db_conn = new mysqli($db_host, $db_username, $db_password, $db_name);
  $query = "SELECT ". $db_stamp. ", ". $db_t1. $db_min. ", ". $db_t1. $db_avg. ", ". $db_t1. $db_max. ", ".
      $db_t2. $db_min. ", ". $db_t2. $db_avg. ", ". $db_t2. $db_max. " FROM ". $db_stat_table;
  $query .= " ORDER BY ". $db_stamp;
  $db_data = $db_conn->query($query);
  $data = array(); 
  while($row = $db_data->fetch_array()) {
    $data[] = array(date("Y-m-d", strtotime($row[$db_stamp])), $row[$db_t1. $db_min], $row[$db_t1. $db_avg], $row[$db_t1. $db_max], $row[$db_t2. $db_min], $row[$db_t2. $db_avg], $row[$db_t2. $db_max]);
  }
  $db_conn->close();
  return $data;
}


// get data from sqlite database
function get_sqlite($limit = 'ALL') {
  global $sqlite_name, $db_table, $db_stamp, $db_t1, $db_t2;

  $db = new SQLite3($sqlite_name);
  $query = "SELECT ". $db_stamp. ", ". $db_t1. ", ". $db_t2. " FROM ". $db_table. "";
  if ($limit != 'ALL')
    $query = $query. " WHERE ". $db_stamp. " BETWEEN datetime('now', '-". $limit. "') AND datetime('now', 'localtime')";
  $query .= " ORDER BY ". $db_stamp. ";";
  #echo $query. PHP_EOL;
  $db_data = $db->query($query);
  $data = array();
  
  while($row = $db_data->fetchArray()) {
    $data[] = array(date("Y-m-d H:i", strtotime($row[$db_stamp])), $row[$db_t1], $row[$db_t2]);
  }
  return $data;
}


// get statistics data from sqlite database
function get_sqlite_statistics() {
  global $sqlite_name, $db_stat_table, $db_t1, $db_t2, $db_min, $db_avg, $db_max, $db_stamp;
    
  $db = new SQLite3($sqlite_name);
  $query = "SELECT ". $db_stamp. ", ". $db_t1. $db_min. ", ". $db_t1. $db_avg. ", ". $db_t1. $db_max. ", ".
      $db_t2. $db_min. ", ". $db_t2. $db_avg. ", ". $db_t2. $db_max. " FROM ". $db_stat_table;
  $query .= " ORDER BY ". $db_stamp;
  $db_data = $db->query($query);
  $data = array(); 
  while($row = $db_data->fetchArray()) {
    $data[] = array(date("Y-m-d", strtotime($row[$db_stamp])), $row[$db_t1. $db_min], $row[$db_t1. $db_avg], $row[$db_t1. $db_max], $row[$db_t2. $db_min], $row[$db_t2. $db_avg], $row[$db_t2. $db_max]);
  }
  return $data;
}


// do this if run from command line (not html)
if (php_sapi_name() == 'cli') {

  if ($argc > 1) {
    if ($argv[1] == '-d') {
      if ($argc > 2) {
        $d = get_sql($argv[2]);
      }
      else {
        $d = get_sql();
      }
      for ($i = 0; $i < count($d); $i ++)
        echo $d[$i][0]. " ". $d[$i][1]. " ". $d[$i][2]. PHP_EOL;
      exit();
    }
  }

  [$t1, $t2] = get_sensors();
  insert_sql($t1, $t2);
}
?>
