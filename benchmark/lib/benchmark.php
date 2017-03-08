<?php

    $memory = array();

    function getMemUsage($msg) {
        global $memory;
        $memory[] = [$msg, memory_get_usage(), memory_get_peak_usage()];
    }

    function getMemUsageReport() {
        global $memory;
        $base_usage = $memory[0][1];
        $base_peak = $memory[0][2];
        echo '<h3>Memory usage</h3>', n;
        echo '<pre>', n;
        echo sprintf('%-20s', 'Measuring point'), ' ',
            sprintf('%20s', 'Memory usage'), ' ',
            sprintf('%20s', 'Memory (relative)'), ' ',
            sprintf('%20s', 'Peak usage'), ' ',
            sprintf('%20s', 'Peak (relative)'), n, n;
        foreach ($memory as $m) {
            echo sprintf('%-20s', $m[0]), ' ',
                sprintf('%18s b', $m[1]), ' ',
                sprintf('%18s b', ($m[1] - $base_usage)), ' ',
                sprintf('%18s b', $m[2]), ' ',
                sprintf('%18s b', ($m[2] - $base_peak)), n;
        }
        echo '</pre>', n;
    }

	/**
	 * This function is based on a similar function found
	 * in a project by Nicolò Martini.
	 * https://github.com/nicmart/StringTemplate
	 */
	function benchmark($data, $iterations)
	{
		global $rows, $delay;

        getMemUsage('Before benchmark');
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			render($data);
		}
		$time = microtime(true) - $start;
		if (isset($delay)) {
			$time -= (($delay / 1000000) * $iterations);
		}
        getMemUsage('After benchmark');
		echo '<h3>Benchmark</h3>', n;
		echo '<pre>', n;
		echo 'Rows      : ', $rows,  n;
		echo 'Iterations: ', $iterations, n;
		echo 'Time      : ', $time, ' s', n;
		echo 'Average   : ', sprintf('%f', ($time / $iterations) * 1000), ' ms', n;
		//echo 'MemoryPeak: ', memory_get_peak_usage(), ' bytes', n;
		echo '</pre>', n;
	}

?>