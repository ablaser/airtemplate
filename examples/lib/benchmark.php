<?php

    $memory = array();

    function get_mem_usage($msg) {
        global $memory;
        $memory[] = [$msg, memory_get_usage(), memory_get_peak_usage()];
    }

    function get_mem_usage_report() {
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
		global $rows;

        get_mem_usage('Before benchmark');
		$start = microtime(true);
		for ($i = 0; $i < $iterations; $i++) {
			render($data);
		}
		$time = microtime(true) - $start;
        get_mem_usage('After benchmark');
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