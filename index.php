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
    <script src="plotly-latest.min.js"></script>
</head>

<body class="home">

    <header id="top">
        <h1>Brodak</h1>
    </header>

    <section class="content">

        <article class="main">
            <h2>Graf</h2>
            <div id='chart'></div>

            <?php // get data
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
                echo PHP_EOL;
                for($i = 0; $i < sizeof($entries); $i ++) {
                    var_dump($entries[$i]);
                    echo "<br>". PHP_EOL;
                }
            ?>
            <!--
                document.getElementById("last_reading_timestamp").innerText = <?php echo "'". date('j.n.Y', strtotime($maxts)). " v ". date('G:i', strtotime($maxts)). "'"; ?>;
                document.getElementById("last_reading_t1").innerText = <?php echo "'". round($lastp[1], 3). "'"; ?>;
                document.getElementById("last_reading_t2").innerText = <?php echo "'". round($lastp[2], 3). "'"; ?>;
                document.getElementById("last_reading_ttot").innerText = <?php echo "'". round($lastp[0], 3). "'"; ?>;
                var tHcol = '#B21B04';
                var tLcol = '#009933';
                var tCcol = '#3333ff';
                <?php
                // tarif high data lines
                $cnt = 0;
                foreach ($watH as $w) {
                    echo "var t1". $cnt. " = {x: [";
                    $cnt += 1;
                    $first = true;
                    foreach ($w as $e) {
                        if ($first) $first = false;
                        else echo ', ';
                        echo "'". $e[0]. "'";
                    }
                    echo "], y: [";
                    $first = true;
                    foreach ($w as $e) {
                        if ($first) $first = false;
                        else echo ', ';
                        echo $e[1];
                    }
                    echo "], name: 'Drahý [kW]', type: 'scatter', mode: 'lines', fill: 'tozeroy', line: {color: tHcol}};". PHP_EOL;
                };
                // tarif low data lines
                $cnt = 0;
                foreach ($watL as $w) {
                    echo "var tL". $cnt. " = {x: [";
                    $cnt += 1;
                    $first = true;
                    foreach ($w as $e) {
                        if ($first) $first = false;
                        else echo ', ';
                        echo "'". $e[0]. "'";
                    }
                    echo "], y: [";
                    $first = true;
                    foreach ($w as $e) {
                        if ($first) $first = false;
                        else echo ', ';
                        echo $e[1];
                    }
                    echo "], name: 'Levný [kW]', type: 'scatter', mode: 'lines', fill: 'tozeroy', line: {color: tLcol}};". PHP_EOL;
                };
                // changes data lines (dots)
                echo "var tC = {x: [";
                $first = true;
                foreach ($changes as $c) {
                    if ($first) $first = false;
                    else echo ', ';
                    echo "'". $c[0]. "'";
                }
                echo "], y: [";
                $first = true;
                foreach ($changes as $c) {
                    if ($first) $first = false;
                    else echo ', ';
                    echo $c[1];
                }
                echo "], name: 'Změny [kW]', type: 'scatter', mode: 'markers', line: {color: tCcol}};". PHP_EOL;
                // list of all data lines to plot
                echo "var data = [";
                $cnt = 0;
                foreach ($watH as $w) {
                    if ($cnt) echo ", ";
                    echo "tH". $cnt;
                    $cnt += 1;
                }
                $cnt = 0;
                foreach ($watL as $w) {
                    echo ", tL". $cnt;
                    $cnt += 1;
                }
                echo "];". PHP_EOL; // no changes dots
                #echo ", tC];". PHP_EOL; // with chages dots
                ?>
                var layout = {
                    yaxis: {
                        title: 'Příkon [kW]'
                    },
                    margin: { t: 0},
                    showlegend: false
                };
                Plotly.newPlot('chart', data, layout);
            </script>-->
        </article>
    </section>
</body>
</html>