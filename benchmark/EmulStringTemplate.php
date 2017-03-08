<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge">
<title>AirPHP Example</title>
<meta name="description" content="{{description}}">
<meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<main>

<?php

	require './lib/bootstrap.php';

    use AirTemplate\Builder;
    use AirTemplate\Loader\ArrayLoader;

	$templates = [
		'main' => '<!doctype html>
<html>
<head>
<title>{{title|esc}}</title>
</head>
<body>
<h1>{{title|esc}}</h1>
<div>
{{content}}
</div>
</body>
</html>
',
		'content' => '<ul>
{{link-list}}
</ul>
<div>
{{body}}
</div>',
		'link-list' => '<li>{{item}}</li>'
	];


	function render() {
		global $engine;

		$main = [
			'title' => 'hello, world',
			'content' => $engine->render(
				'content',
				[
					'body' => 'Hi, sup',
					'link-list' => $engine->each(
						'link-list',
						['one', 'two', 'three'],
						"\n"
					)
				]
			),
		];
		return $engine->render('main', $main);
	}

    $builder = new Builder(new ArrayLoader);
    $engine = $builder->build($templates);
    $builder = null;

    echo '<h2>StringTemplate benchmark emulation</h2>', n;
    echo '<p>
This is a AirTemplate version of the engine benchmark test of [StringTemplate](https://github.com/nicmart/StringTemplate).<br>
</p>
';

	echo render();

	echo '<pre>', n, n;

	benchmark([], 100000);

	echo n, '</pre>', n;

?>

</main>
<footer>
<p>&copy; 2016 Andreas Blaser</p>
</footer>
</body>
</html>
