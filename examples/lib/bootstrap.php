<?php
/**
 * An example of a project-specific implementation.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \AirTemplate\MemTemplate class
 * from /AirTemplate/src/MemTemplate.php:
 *
 *      new \AirTemplate\MemTemplate
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'AirTemplate\\';

    // base directory for the namespace prefix
    $base_dir = realpath(__DIR__ . '/../../src/') . DIRECTORY_SEPARATOR;

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");
if (!defined('PERF_TEST_ITERATIONS')) define('PERF_TEST_ITERATIONS', 1000);

require 'TestdataGenerator.php';
