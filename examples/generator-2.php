<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title>AirTemplate Examples</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<main>

<?php

	require './lib/bootstrap.php';
	require './lib/benchmark.php';

	use AirTemplate\Template;

	$templates = array(
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
<td>{{id}}</td>
<td>{{value}}</td>
<td>{{desc}}</td>
</tr>',
	);

	function render($data)
	{
		global $engine, $filename;

		$rows = 0;
		$fp = fopen($filename, 'w');

		// Row consumer method (PHP Generator)
		// Append rendered lines to the output file
		$writeRow = function() use ($rows, $fp) {
			while (true) {
				fwrite($fp, yield);
				$rows++;
			}
		};

		// Table start
		$table_data = array(
			'id' => 'testdata',
			'thead' => $engine->each('th', array('ID', 'Value', 'Description')),
		);
		fwrite($fp, $engine->render('table_begin', $table_data));
		// Build table body using the closure $writeRow()
		$engine->each('tr', $data, array('desc' => 'htmlspecialchars'), n, $writeRow());
		// End of table
		fwrite($fp, n . $engine->render('table_end'));
		fclose($fp);
	}


	if (isset($_GET['iterations'])) {
		$iterations = is_numeric($_GET['iterations'])
			? intval($_GET['iterations'])
			: 10;
		if ($iterations > 1000) {
			$iterations = 10;
		}
	} else {
		$iterations = 0;
	}

	// create testdata
	$rows = 10000;
	$testdata = new TestdataGenerator($rows);

	$filename = './cache/big-table.html';

	// create the template object and load templates
    get_mem_usage('Start');
	$engine = new Template;
    get_mem_usage('Instance created');
	$engine->setTemplates($templates);
    get_mem_usage('Templates loaded');

	echo '<h2>Generator (2)</h2>', n;
	echo '<p>This example produces a big HTML-table and writes it to a file.</p>', n;
	echo '<p>Filename: ' . $filename . '</p>', n;
	echo '<p>Some notes:</p>', n, '<ul>';
	echo '<li>The <b>cache</b> directory must be writeable.</li>', n;
	echo '<li>Please <strong>do not open the output file in the browser</strong>, as it may crash. Use a text editor instead.</li>', n;
	echo '<li>Runtime includes the file I/O.</li>', n;
	echo '</ul>', n;


	if ($iterations > 0) {
		benchmark($testdata, $iterations);
	} else {
		get_mem_usage('Before render');
		$start = microtime(true);
		render($testdata);
		$time = microtime(true) - $start;
		get_mem_usage('After render');
		echo '<h3>Statistics</h3>', n;
		echo '<pre>', n;
		echo 'Rows         : ', $rows,  n;
		echo 'Time         : ', sprintf('%f', $time * 1000), ' ms', n;
		echo '</pre>', n;
	}

    get_mem_usage_report();
    echo '<h3>Run benchmark</h3>', n;
	echo '<p><a href="./generator-2.php?iterations=10">Run benchmark test 10 times</a></p>', n;
	echo '<p><a href="./generator-2.php?iterations=100">Run benchmark test 100 times</a></p>', n;

?>

</main>
<footer>
<p>&copy; 2016 Andreas Blaser</p>
</footer>
</body>
</html>
