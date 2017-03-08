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
		'table_begin' => '<table id="{{id}}">
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
		'th' => '<th>{{item}}</th>',
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
		global $engine, $filename, $delay;

        if (count($data) > 0) {
			$fp = @fopen($filename, 'w');

			// Row consumer method (PHP Generator)
			// Append rendered lines to the output file
			$rows = 0;
			$writeRow = function() use ($rows, $fp) {
				while (true) {
					fwrite($fp, yield);
					$rows++;
				}
			};

			// Table start
			$table_data = array(
				'thead' => $engine->each('th', array_keys($data[0])),
			);
			fwrite($fp, $engine->render('table_begin', $table_data));
			// Build table body using the closure $writeRow()
			$engine->each('tr', $data, PHP_EOL, $writeRow());
			// End of table
			fwrite($fp, PHP_EOL . $engine->render('table_end'));
			@fclose($fp);
			usleep($delay);
			return;
		}
        return $engine->render('no_data');
	}


	$args = getArgs();

	// create testdata
    $testdata = json_decode(file_get_contents('./data/medline_2016_flat.json'), true);
    $rows = count($testdata);

	$filename = './temp/big-table.html';

    getMemUsage('Start');
    $builder = new Builder(new ArrayLoader);
    $engine = $builder->build($templates);
    $builder = null;
    getMemUsage('Instance created');

    echo '<h2>Using the generator mode (2)</h2>', n;
	echo '<p>This example produces a HTML-table with 168 rows and writes it to a file.<br>
Filename: ', $filename, '</p>', n;
	echo '<p>Some notes:</p>
<ul>
<li>The <b>cache</b> directory must be writeable.</li>
<li>The runtime shown includes the file I/O.</li>
</ul>
';

    echo '<h3>Result</h3>', n;
    getMemUsage('Before render');
    render($testdata);
    getMemUsage('After render');
	@readfile($filename);

	if ($args->iterations > 0) {
		$delay = 50; // delay after file close in microseconds
		benchmark($testdata, $args->iterations);
	}

    getMemUsageReport();

    echo '<h3>Run benchmark</h3>', n;
    echo '<p><a href="./' . basename(__FILE__) . '?iterations=100">Run 100 times</a></p>', n;
    echo '<p><a href="./' . basename(__FILE__) . '?iterations=1000">Run 1000 times</a></p>', n;

?>
</main>
<footer>
<p>&copy; 2016 Andreas Blaser</p>
</footer>
</body>
</html>
