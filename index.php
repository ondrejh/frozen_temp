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

    <header id="top">
        <h1>Brodak</h1>
    </header>

    <section class="content">

        <article class="main">
            <h2>Graf</h2>
            <div id='chart'></div>
            <?php
              echo "<script>".PHP_EOL;
              echo "var trace1 = {x: [1, 2, 3, 4], y: [10, 15, 13, 17], type: 'scatter'}; var trace2 = {x: [1, 2, 3, 4], y: [16, 5, 11, 9], type: 'scatter'}; var data = [trace1, trace2]; Plotly.newPlot('chart',data);".PHP_EOL;
              echo "</script>". PHP_EOL;
            ?>

            <!--// get data
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
                error_reporting(E_ALL);

                $db = new SQLite3("data.sql");
                $query = "SELECT * FROM 'readings' WHERE 'stamp' > datetime('now', '-3 days')";
                $data = $db->query($query);
                $entries = array();
                while($row = $data->fetchArray()) {
                    $entries[] = array(date("Y-m-d H:i", strtotime($row['stamp'])), $row['t1'], $row['t2']);
                }
                echo "<script>". PHP_EOL;
                echo "var t1 = {x [";
                $first = True;
                for($i = 0; $i < sizeof($entries); $i ++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo "'". $entries[$i][0]. "'";
                }
                echo "], y [";
                $first = True;
                for($i = 0; $i < sizeof($entries); $i ++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo $entries[$i][1];
                }
                echo "], name: 't1', type: 'scatter', mode: 'lines', fill: 'tozeroy'";
                echo "};".PHP_EOL;
                echo "var t2 = {x [";
                $first = True;
                for($i = 0; $i < sizeof($entries); $i ++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo "'". $entries[$i][0]. "'";
                }
                echo "], y [";
                $first = True;
                for($i = 0; $i < sizeof($entries); $i ++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo $entries[$i][2];
                }
                echo "], name: 't2', type: 'scatter', mode: 'lines', fill: 'tozeroy'";
                echo "};". PHP_EOL;
                echo "var data = [t1, t2];". PHP_EOL;
                echo "var layout = {yaxis: {title: 'ahoj [stC]'}, margin: { t: 0}, showlegend: false };". PHP_EOL;
                echo "Plotly.newPlot('chart', data, legend); </script>". PHP_EOL;
            -->
        </article>
    </section>
</body>
</html>