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
{{thead|each("th")}}
</tr>
</thead>
<tbody>
{{tbody|each("tr", "\n")}}
</tbody>
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
            // prepare table data
            // fill thead and tbody with raw array data
            $table_data = [
                'thead' => array_keys($data[0]),
                'tbody' => $data,
            ];
            // return rendered table
            return $engine->render('table', $table_data);
        }
        return $engine->render('no_data');
    }


    $args = getArgs();
    $testdata = json_decode(file_get_contents('./data/medline_2016_flat_10.json'), true);
    $rows = count($testdata);

    getMemUsage('Start');
    // This builder creates a render engine from templates in memory
    $builder = new Builder(new ArrayLoader);
    // Build the render engine for the templates
    $engine = $builder->build($templates);
    // We don't need it anymore...
    $builder = null;
    getMemUsage('Instance created');

    echo '<h2>Basic Usage With Nested Templates</h2>', n;
    echo '<p>
Business logic is controlled from PHP, but the view logic has been delegated to the render engine.<br>

Field option <code>each</code> is used in the <code>table</code> template for the fields <code>thead</code> and <code>tbody</code>.<br>
The corresponding fields in the data parameter must contain arrays or "iterable" objects.
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
