<?php

    require __DIR__ . '/../../vendor/autoload.php';

	if (!defined('n')) define('n', "\n");
	if (!defined('br')) define('br', "<br>");
	if (!defined('PERF_TEST_ITERATIONS')) define('PERF_TEST_ITERATIONS', 1000);

    require __DIR__ . '/benchmark.php';
    require __DIR__ . '/getargs.php';
    require __DIR__ . '/UserLib.php';
