<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title>AirTemplate Example</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<main>
<?php
	require './lib/bootstrap.php';

    use AirTemplate\Builder;
    use AirTemplate\Loader\ArrayLoader;

    $templates = [
        'table_begin' => '<table id="{{id|esc}}">
<thead>
<tr>
{{thead}}
</tr>
</thead>
<tbody>
',
        'table_end' => '</tbody>
</table>
',
        'th' => '<th>{{item|esc}}</th>',
        'tr' => '<tr>
<td>{{MedlineTA|esc}}</td>
<td>{{DateCreated}}</td>
<td>{{Volume}}</td>
<td>{{Issue}}</td>
<td>{{Language}}</td>
<td>{{ArticleTitle|\UserLib::truncateWords(?, 50, " &hellip;")}}</td>
</tr>',
        'no_data' => '<p>Sorry, there is no data to display.</p>'
    ];


    function render($data)
    {
        global $engine;

        if (count($data) > 0) {
			// The output is kept in a string buffer for this example.
			// In reality you may want to write the generated output immediatly to a stream.
			// see example TestGeneratorFile.php
			$html = '';

			// This closure is a PHP generator function and will receive all
			// rendered rows, one by one.
			// It is passed to the repeat function as the 4th argument (see below).
			$processRows = function() use (&$html) {
				while (true) {
					$html .= yield;
				}
			};

			// Table start
			$table_data = array(
				'thead' => $engine->each('th', array_keys($data[0])),
			);
			$html .= $engine->render('table_begin', $table_data);
			// Render table body using the closure above.
			// Note that parentheses () must be used here!
			$engine->each('tr', $data, n, $processRows());
			// Table end
			$html .= n . $engine->render('table_end');
			return $html;
		}
        return $engine->render('no_data');
    }


    $args = getArgs();
    $testdata = json_decode(file_get_contents('./data/medline_2016_flat_10.json'), true);
    $rows = count($testdata);

    getMemUsage('Start');
    $builder = new Builder(new ArrayLoader);
    $engine = $builder->build($templates);
    $builder = null;
    getMemUsage('Instance created');

    echo '<h2>Using the generator mode</h2>', n;
    echo '<p>
Normally, the engine renders arrays in memory, which may be memory consuming when rendering large arrays.<br>
To circumvent this, AirTemlate supports the generator mode. A generator function receives rendered rows<br>
<strong>one by one</strong>, so they must not be accumulated in memory. This has not much effect with only 10 rows,<br>
but with hundreds of rows&hellip;
</p>
';

    echo '<h3>Result</h3>', n;
    getMemUsage('Before render');
    echo render($testdata);
    getMemUsage('After render');

    if ($args->iterations > 0) {
        benchmark($testdata, $args->iterations);
    }

    getMemUsageReport();

    echo '<h3>Run benchmark</h3>', n;
    echo '<p><a href="./' . basename(__FILE__) . '?iterations=1000">Run 1000 times</a></p>', n;
    echo '<p><a href="./' . basename(__FILE__) . '?iterations=10000">Run 10000 times</a></p>', n;

?>
</main>
<footer>
<p>&copy; 2016 Andreas Blaser</p>
</footer>
</body>
</html>
