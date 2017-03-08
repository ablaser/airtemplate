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
<td>{{DateCreated}}</td>
<td>{{VolumeIssue|user:getVolumeIssue}}</td>
<td>{{Language}}</td>
<td>{{ArticleTitle|\UserLib::truncateWords(?, 50, " &hellip;")}}</td>
</tr>',
//        'tr' => '<tr>
//<td>{{MedlineTA|user:addVolumeIssue|esc}}</td>
//<td>{{DateCreated}}</td>
//<td>{{VolumeIssue}}</td>
//<td>{{Language}}</td>
//<td>{{ArticleTitle|\UserLib::truncateWords(?, 50, " &hellip;")}}</td>
//</tr>',
        'no_data' => '<p>Sorry, there is no data to display.</p>'
    ];


    /**
     * Create a combined value from Volume and Issue.
     *
     * This is a AirTemplate user function that has a standard
     * parameter list.
     *
     * @param string $value The input string
     * @param string $field Maximum string length
     * @param $array $data  The data array
     *
     * @return string
     */
    function getVolumeIssue($value, $field, $data)
    {
        if (!empty($data['Volume'])) {
            if (!empty($data['Issue'])) {
                return $data['Volume'] . '/' . $data['Issue'];
            }
            return $data['Volume'];
        }
        return $data['Issue'];
    };

    // This is an alternative approach:
    // Create a new field when 'MedlineTA' is processed and add it to
    // the data array.
    // NOTE: $data MUST be passed by reference (&$data), otherwise
    // the created field is lost when the function returns.
    //function addVolumeIssue($value, $field, &$data)
    //{
    //    if (!empty($data['Volume'])) {
    //        if (!empty($data['Issue'])) {
    //            $data['VolumeIssue'] = $data['Volume'] . '/' . $data['Issue'];
    //        } else {
    //            $data['VolumeIssue'] = $data['Volume'];
    //        }
    //    } else {
    //        $data['VolumeIssue'] = $data['Issue'];
    //    }
    //    return $value;
    //};

    function render($data)
    {
        global $engine;

        if (count($data) > 0) {
            // prepare table data
            $table_data = [
                'thead' => $engine->each(
                    'th',
                    ['MedlineTA', 'DateCreated', 'VolumeIssue', 'Language', 'ArticleTitle']
                ),
                'tbody' => $engine->each('tr', $data, n)
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
    $builder = new Builder(new ArrayLoader);
    $engine = $builder->build($templates);
    $builder = null;
    getMemUsage('Instance created');

    echo '<h2>Create a derieved field</h2>', n;
    echo '<p>
Shows how to render a HTML Table with a dynamically created field (column).<br>
The new field is created through a user function <code>getVolumeIssue</code>, which is applied in the template \'tr\'.<br>
The source code contains also an alternative approach (commented), which creates the field and adds<br>
it to the data array, so it can be rendered later when this field is processed.
</p>
';
    //if ($args->iterations == 0) {
    //    echo '<h3>Data structure</h3>', n, '<pre>', n;
    //    echo var_dump($testdata[0]);
    //    echo '</pre>', n;
    //}
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
