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
        'table' => '<table>
<thead>
<tr>
{{thead}}
</tr>
</thead>
<tbody>
{{tbody}}
</tbody>
</table>
',
        'th' => '<th>{{item|esc}}</th>',
        'tr' => '<tr>
<td>{{MedlineTA|esc}}</td>
<td>{{DateCreated|render("date")}}</td>
<td>{{Volume}}</td>
<td>{{Issue}}</td>
<td>{{Language}}</td>
<td>{{ArticleTitle|\UserLib::truncateWords(?, 50, " &hellip;")}}</td>
</tr>',
		'date' => '{{Year}}-{{Month}}-{{Day}}',
        'no_data' => '<p>Sorry, there is no data to display.</p>'
    ];


    function render($data)
    {
        global $engine;

        if (count($data) > 0) {
			// prepare table data
			$table_data = array(
				'thead' => $engine->each('th', array_keys($data[0])),
				'tbody' => $engine->each('tr', $data, n)
			);
			// return rendered table
			return $engine->render('table', $table_data);
        }
        return $engine->render('no_data');
    }

    $args = getArgs();
    $testdata = json_decode(file_get_contents('./data/medline_2016_nested_10.json'), true);
    $rows = count($testdata);

    getMemUsage('Start');
    $builder = new Builder(new ArrayLoader);
    $engine = $builder->build($templates);
    $builder = null;
    getMemUsage('Instance created');

    echo '<h2>Accessing nested values</h2>', n;
    echo '<p>
This is a programmatic way to access nested values in the data array (or object).<br>
The field <code>DateCreated</code> has a sub-structure which will be processed through
a nested template:
</p>
';
    if ($args->iterations == 0) {
        echo '<h3>Data structure</h3>', n, '<pre>', n;
        echo var_dump($testdata[0]);
        echo '</pre>', n;
    }
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
