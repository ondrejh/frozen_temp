<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex, nofollow">
    <meta name="rating" content="general">
    <meta name="author" content="ondrejh, tomasb">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Brodak</title>
    <meta name="description" content="Vizualizace chladu." />
    <meta name="keywords" content="zima, kureuska" />
    <link rel="shortcut icon" href="favicon.ico" />
    <link rel="stylesheet" type="text/css" href="style.css" media="screen, print" />
    <link rel="stylesheet" type="text/css" href="local_style.css" media="screen, print" />
    <script src="plotly.min.js"></script>
</head>

<body class="home">

    <section class="content">

        <article class="main">
            <h2>Graf měření</h2>
            <div id='chart'></div>

            <h2>Graf statistiky</h2>
            <div id='chart_stat'></div>

            <?php
                include "get_data.php";

                $mysql_version = true;
                if (isset($_GET["type"])) {
                    if ($_GET["type"] === 'mysql') $mysql_version = true;
                    if ($_GET["type"] === 'sqlite') $mysql_version = false;
                }

                $data = array();
                $stat = array();

                if ($mysql_version === true) {
                    $data = get_sql('7 DAY');
                    $stat = get_sql_statistics();
                }
                else {
                    $data = get_sqlite('7 days');
                    $stat = get_sqlite_statistics();
                }
            ?>

            <div class='left'>
                <h2>Hodnoty měření</h2>
                <table>
                    <tr>
                        <th>Datum a čas</th>
                        <th class="center green">Voda [°C]</th>
                        <th class="center blue">Vzduch [°C]</th>
                    </tr>
                    <?php
                        for ($i=(sizeof($data)-1); $i>0; $i--)
                            echo "<tr><td>". $data[$i][0]. '</td><td>'. $data[$i][1]. '</td><td>'. $data[$i][2]. '</td></tr>'.PHP_EOL;
                    ?>
                </table>
            </div>
            <div class='right'>
                <h2>Denní statistika</h2>
                <table>
                    <tr>
                        <th>&nbsp;</th>
                        <th colspan="3" class="center green">Voda [°C]</th>
                        <th colspan="3" class="center blue">Vzduch [°C]</th>
                    </tr>
                    <tr>
                        <th>Datum</th>
                        <th>MIN</th>
                        <th>AVG</th>
                        <th>MAX</th>
                        <th>MIN</th>
                        <th>AVG</th>
                        <th>MAX</th>
                    </tr>
                    <?php
                        for ($i=(sizeof($stat)-1); $i>0; $i--) {
                            echo "<tr>";
                            for ($c=0; $c<7; $c++)
                                echo "<td>". $stat[$i][$c]. "</td>";
                            echo "</tr>". PHP_EOL;
                        }
                    ?>
                </table>
            </div>
        </article>
    </section>

    <script>
        // graf mereni
        var voda_col = '#32CD32';
        var vzduch_col = '#3399ff';
        <?php
            $data_x = ''; $first = True;
            for($i=1; $i<sizeof($data); $i++) {
                if (! $first) $data_x .= ", "; else $first = False;
                $data_x .= "'". $data[$i][0]. "'";
            }
        ?>
        var t1 = {x: [ <?php echo $data_x; ?> ], y: [ <?php
            $first = True;
            for($i=1; $i<sizeof($data); $i++) {
                if (! $first) echo ", ";
                else $first = False;
                echo $data[$i][1];
            }
        ?> ], name: 'voda', type: 'scatter', mode: 'lines', fill: 'tozeroy', line: {color: voda_col}};
        var t2 = {x: [ <?php echo $data_x; ?> ], y: [ <?php
            $first = True;
            for($i=1; $i<sizeof($data); $i++) {
                if (! $first) echo ", ";
                else $first = False;
                echo $data[$i][2];
            }
        ?> ], name: 'vzduch', type: 'scatter', mode: 'lines', fill: 'tozeroy', line: {color: vzduch_col}};
        var data = [t2, t1];
        var layout = {legend: {x: 0, y: 1}, yaxis: {title: 'teplota [°C]'}, margin: { t: 0}};
        Plotly.newPlot('chart', data, layout);
    </script>

    <script>
        // graf statistiky
        var voda_col = '#32CD32';
        var vzduch_col = '#3399ff';
        <?php
            $stat_x = ''; $first = True;
            for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) $stat_x .= ", "; else $first = False;
                $stat_x .= "'". $stat[$i][0]. "'";
            }
        ?>
        var t1min = {x: [ <?php echo $stat_x; ?> ], y: [ <?php
            $first = True; for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) echo ", "; else $first = False;
                echo $stat[$i][1];
            }
        ?> ], line: {width: 0}, marker: {color: voda_col}, mode: "lines", name: "min", type: "scatter", showlegend: false};
        var t1max = {x: [ <?php echo $stat_x; ?> ], y: [ <?php
            $first = True; for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) echo ", "; else $first = False;
                echo $stat[$i][3];
            }
        ?> ], fill: "tonexty", line: {width: 0}, marker: {color: voda_col}, mode: "lines", name: "max", type: "scatter", showlegend: false};
        var t1avg = {x: [ <?php echo $stat_x; ?> ], y: [ <?php
            $first = True; for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) echo ", "; else $first = False;
                echo $stat[$i][2];
            }
        ?> ], marker: {color: voda_col}, mode: "lines", name: "voda", type: "scatter", showlegend: true};
        var t2min = {x: [ <?php echo $stat_x; ?> ], y: [ <?php
            $first = True; for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) echo ", "; else $first = False;
                echo $stat[$i][4];
            }
        ?> ], line: {width: 0}, marker: {color: vzduch_col}, mode: "lines", name: "min", type: "scatter", showlegend: false};
        var t2max = {x: [ <?php echo $stat_x; ?> ], y: [ <?php
            $first = True; for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) echo ", "; else $first = False;
                echo $stat[$i][6];
            }
        ?> ], fill: "tonexty", line: {width: 0}, marker: {color: vzduch_col}, mode: "lines", name: "max", type: "scatter", showlegend: false};
        var t2avg = {x: [ <?php echo $stat_x; ?> ], y: [ <?php
            $first = True; for($i=1; $i<sizeof($stat); $i++) {
                if (! $first) echo ", "; else $first = False;
                echo $stat[$i][5];
            }
        ?> ], marker: {color: vzduch_col}, mode: "lines", name: "vzduch", type: "scatter", showlegend: true};
        var data = [t2min, t2max, t1min, t1max, t1avg, t2avg];
        var layout = {legend: {x: 0, y: 1}, yaxis: {title: 'teplota [°C]'}, margin: { t: 0}};
        Plotly.newPlot('chart_stat', data, layout);
    </script>

    <script>document.title = "Brodak (<?php if ($mysql_version === true) {echo "MySQL";} else {echo "SqLite";}?>)"</script>
</body>
</html>
