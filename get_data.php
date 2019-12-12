<?php

$db_host = 'localhost';
$db_username = 'admin';
$db_password = '1243';

$db_name = 'votuzilec';

$db_table = 'teplomer';
$db_t1 = 't1';
$db_t2 = 't2';
$db_stamp = 'stamp';


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
  echo $query. PHP_EOL;
  $result = $db_conn->query($query);
  $ret = $result->fetch_all();
  $db_conn->close();

  return $ret;
}


// do this if run from command line (not html)
if (php_sapi_name() == 'cli') {

  if ($argc > 1) {
    if ($argv[1] == '-d') {
      if ($argc > 2) {
        $d = get_sql($argv[2]);
        var_dump($d);
      }
      else {
        $d = get_sql();
        var_dump($d);
      }
      exit();
    }
  }

  insert_sql(12.3, -8.5);

}

?>
