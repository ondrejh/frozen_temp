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
                $db = new SQLite3("data.sql");
                $query = "SELECT * FROM 'readings' WHERE 'stamp' > datetime('now', '-3 days')";
                $data = $db->query($query);
                $entries = array();
                while($row = $data->fetchArray()) {
                    $entries[] = array(date("Y-m-d H:i", strtotime($row['stamp'])), $row['t1'], $row['t2']);
                }

                # polozky z databaze
                echo "<h2>Vypis</h2>". PHP_EOL;
                for ($i=1; $i<sizeof($entries); $i++) {
                    echo var_dump($entries[$i]);
                    echo '<br>'.PHP_EOL;
                }
            
                # vykresleni grafu:
                echo "<script>". PHP_EOL;
                echo "var t1 = {x: [";
                $first = True;
                for($i=1; $i<sizeof($entries); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo "'". $entries[$i][0]. "'";
                }
                echo "], y: [";
                $first = True;
                for($i=1; $i<sizeof($entries); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo $entries[$i][1];
                }
                echo "], type: 'scatter'};".PHP_EOL;
                echo "var t2 = {x: [";
                $first = True;
                for($i=1; $i<sizeof($entries); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo "'". $entries[$i][0]. "'";
                }
                echo "], y: [";
                $first = True;
                for($i=1; $i<sizeof($entries); $i++) {
                    if (! $first) echo ", ";
                    else $first = False;
                    echo $entries[$i][2];
                }
                echo "], type: 'scatter'};". PHP_EOL;
                echo "var data = [t1, t2];". PHP_EOL;
                echo "Plotly.newPlot('chart', data); </script>". PHP_EOL;
            ?>
        </article>
    </section>
</body>
</html>