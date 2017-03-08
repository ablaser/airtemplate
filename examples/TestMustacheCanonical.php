<?php
	header('Content-Type: text/plain');

	require './lib/bootstrap.php';
	require './lib/Chris.php';

    use AirTemplate\Builder;
    use AirTemplate\Loader\ArrayLoader;

	$templates = [
		'canonical' => 'Hello {{name}}
You have just won {{value}} dollars!
',
		'in_ca' => 'Well, {{taxed_value|data:taxed_value}} dollars, after taxes.
'
	];

	$chris = new Chris;

	$builder = new Builder(new ArrayLoader);
	$engine = $builder->build($templates);

	echo $engine->render('canonical', $chris);
	if ($chris->in_ca == true) {
		echo $engine->render('in_ca', $chris);
	}
