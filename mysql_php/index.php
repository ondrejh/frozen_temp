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
                include "get_data.php";
            
                $data = get_sql('7 DAY');
            
                echo "<p>". PHP_EOL;
                for ($i = 0; $i < sizeof($data); $i++)
                    echo $data[$i][0]. " ". $data[$i][1]. " ". $data[$i][2]. "<br>". PHP_EOL;
                echo "</p>". PHP_EOL;
            
                # vykresleni grafu:
                echo "<script>". PHP_EOL;
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
                echo "], type: 'scatter'};".PHP_EOL;
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
                echo "], type: 'scatter'};". PHP_EOL;
                echo "var data = [t1, t2];". PHP_EOL;
                echo "Plotly.newPlot('chart', data); </script>". PHP_EOL;
            ?>
        </article>
    </section>
</body>
</html>