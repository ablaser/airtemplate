<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title>AirTemplate Examples</title>
<meta name="description" content="{{description}}">
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
        global $engine;

        // The output is kept in a string for this example.
        // In reality you may want to echo the generated output immediatly.
        $html = '';

        // This closure is a PHP generator function and will receive all
        // rendered rows, one by one.
        // It is passed to the repeat function as the 5-th argument (see below).
        $processRows = function() use (&$html) {
            while (true) {
                $html .= yield;
            }
        };

        // Table start
        $table_data = array(
            'id' => 'testdata',
            'thead' => $engine->each('th', array('ID', 'Value', 'Description')),
        );
        $html .= $engine->render('table_begin', $table_data);
        // Render table body using the closure above.
        // Note that parentheses () must be used here!
        $engine->each('tr', $data, array('desc' => 'htmlspecialchars'), n, $processRows());
        // Table end
        $html .= n . $engine->render('table_end');
        return $html;
    }


    if (isset($_GET['iterations'])) {
        $iterations = is_numeric($_GET['iterations'])
            ? intval($_GET['iterations'])
            : PERF_TEST_ITERATIONS;
        if ($iterations > 100000) {
            $iterations = PERF_TEST_ITERATIONS;
        }
    } else {
        $iterations = 0;
    }

    // create testdata
    $rows = 10;
    $testdata = new TestdataGenerator($rows);

    // create the template object and load templates
    get_mem_usage('Start');
    $engine = new Template;
    get_mem_usage('Instance created');
    $engine->setTemplates($templates);
    get_mem_usage('Templates loaded');

    echo '<h2>Generator</h2>', n;
    echo 'Using a PHP generator function with <code>each</code> to create the table body.', n;

    echo n, '<pre>', n, n;
    get_mem_usage('Before render');
    echo render($testdata);
    get_mem_usage('After render');
    echo n, '</pre>', n;

    if ($iterations > 0) {
        benchmark($testdata, $iterations);
    }

    get_mem_usage_report();
    echo '<h3>Run benchmark</h3>', n;
    echo '<p><a href="./generator.php?iterations=1000">Run benchmark test 1000 times</a></p>', n;
    echo '<p><a href="./generator.php?iterations=10000">Run benchmark test 10000 times</a></p>', n;

?>

</main>
<footer>
<p>&copy; 2016 Andreas Blaser</p>
</footer>
</body>
</html>
