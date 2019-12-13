<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="rating" content="general">
    <meta name="author" content="ondrejh">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Brodak</title>
    <meta name="description" content="Vizualizace chladu." />
    <meta name="keywords" content="zima, kureuska" />
    <link rel="stylesheet" type="text/css" href="style.css" media="screen, print" />
    <script src="plotly.min.js"></script>
</head>

<body class="home">

    <section class="content">

        <article class="main">
            <h2>Graf (<span id='db_type'></span>)</h2>
            <div id='chart'></div>
            <?php
                include "get_data.php";
            
                $data = array();
                
                $mysql_version = true;
                if (isset($_GET["type"])) {
                    if ($_GET["type"] === 'mysql') $mysql_version = true;
                    if ($_GET["type"] === 'sqlite') $mysql_version = false;
                }
                
                if ($mysql_version === true)
                    $data = get_sql('7 DAY');
                else
                    $data = get_sqlite('7 DAY');

                # polozky z databaze
                echo "<h2>Vypis</h2>". PHP_EOL. "<table>". PHP_EOL;
                for ($i=1; $i<sizeof($data); $i++)
                    echo "<tr><td>". $data[$i][0]. '</td><td>'. $data[$i][1]. '</td><td>'. $data[$i][2]. '</td></tr>'.PHP_EOL;
                echo "</table>". PHP_EOL;
            
                # vykresleni grafu:
                echo "<script>". PHP_EOL. "var voda_col = '#0033cc';". PHP_EOL. "var vzduch_col = '#3399ff';". PHP_EOL;
                echo "var t1 = {x: [";
                $first = True;
                for($i=1; $i<sizeof($data); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo "'". $data[$i][0]. "'";
                }
                echo "], y: [";
                $first = True;
                for($i=1; $i<sizeof($data); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo $data[$i][1];
                }
                echo "], name: 'voda', type: 'scatter', mode: 'lines', fill: 'tozeroy', line: {color: voda_col}};".PHP_EOL;
                echo "var t2 = {x: [";
                $first = True;
                for($i=1; $i<sizeof($data); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo "'". $data[$i][0]. "'";
                }
                echo "], y: [";
                $first = True;
                for($i=1; $i<sizeof($data); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo $data[$i][2];
                }
                echo "], name: 'vzduch', type: 'scatter', mode: 'lines', fill: 'tozeroy', line: {color: vzduch_col}};". PHP_EOL;
                echo "var data = [t1, t2];". PHP_EOL;
                echo "var layout = {legend: {x: 0, y: 1}, yaxis: {title: 'teplota [°C]'}, margin: { t: 0}};". PHP_EOL;
                echo "Plotly.newPlot('chart', data, layout); </script>". PHP_EOL;
            ?>
            <script>document.getElementById("db_type").innerText = "<?php if ($mysql_version === true) {echo "MySQL";} else {echo "SqLite";}?>"</script>
        </article>
    </section>
</body>
</html>