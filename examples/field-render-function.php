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
        'table' => '<table id="{{id}}">
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
        'th' => '<th>{{item}}</th>',
        'tr' => '<tr>
<td>{{id}}</td>
<td>{{value}}</td>
<td>{{desc}}</td>
<td>{{computed_field}}</td>
</tr>',
    );


    function render($data)
    {
        global $engine;

        // create and return the value for field 'computed_field'
        $computed_field = function($value, $field, $data, $isObject) {
            return htmlspecialchars('This is id ' . $data['id'] . '.');
        };

        // Another way to do this is to manipulate the $data array.
        // The new field is added to the data array, when the 'id' field
        // is processed and the id field itself is returned.
        // This method may be useful if multiple fields are created, because
        // it can be done in a single function call.
        // Note: The data array must be passed by reference (&$data).
        $id = function($value, $field, &$data, $isObject) {
            $data['computed_field'] = htmlspecialchars('This is id ' . $value . '.');
            return htmlspecialchars($value);
        };


        // prepare table data
        // Note the absence of parentheses () in the reference to the closure.
        $table_data = array(
            'id' => 'testdata',
            'thead' => $engine->each(
                'th',
                array('ID', 'Value', 'Description', 'Computed Field')
            ),
            'tbody' => $engine->each(
                'tr',
                $data,
                array(
                    'desc' => 'htmlspecialchars',
                    'id' => 'htmlspecialchars',
                    'computed_field' => $computed_field,
                    // to use the id-closure, uncomment the line below
                    // AND comment out the two lines above this comment
                    //'id' => $id,
                ),
                n
            )
        );
        // return rendered table
        return $engine->render('table', $table_data);
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

    // create the template object
    get_mem_usage('Start');
    $engine = new Template;
    get_mem_usage('Instance created');
    $engine->setTemplates($templates);
    get_mem_usage('Templates loaded');

    echo '<h2>Custom field render function</h2>', n;
    echo '<p>Using a custom field render function to create a computed field.</p>', n;

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
    echo '<p><a href="./field-render-function.php?iterations=1000">Run benchmark test 1000 times</a></p>', n;
    echo '<p><a href="./field-render-function.php?iterations=10000">Run benchmark test 10000 times</a></p>', n;

?>

</main>
<footer>
<p>&copy; 2016 Andreas Blaser</p>
</footer>
</body>
</html>
