<?php

    function getArgs() {
        $args = new stdClass;
        $args->iterations = 0;
        if (isset($_GET['iterations'])) {
            $args->iterations = is_numeric($_GET['iterations'])
                ? intval($_GET['iterations'])
                : PERF_TEST_ITERATIONS;
            if ($args->iterations > 100000) {
                $args->iterations = PERF_TEST_ITERATIONS;
            }
        }
        return $args;
    }
