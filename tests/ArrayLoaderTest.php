<?php

use PHPUnit\Framework\TestCase;
use AirTemplate\Loader\ArrayLoader;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");

class ArrayLoaderTest extends PHPUnit_Framework_TestCase
{

    public function testArrayLoader()
    {

        $templates = [
            'test' => '<b>{{var1}} {{var2|esc}}</b>'
        ];

        // Act
        $loader = new ArrayLoader;
        $parsed = $loader->load($templates);

        // Assert
        $this->assertTrue(is_array($parsed));
        $this->assertEquals(1, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);
        $this->assertEquals(0, count($parsed['test']['options']['var1']));
        $this->assertEquals(1, count($parsed['test']['options']['var2']));
        $this->assertEquals('esc', $parsed['test']['options']['var2'][0]);
    }

    public function testArrayLoaderCustomDelim()
    {

        $templates = [
            'test' => '<b>[@var1] [@var2|esc]</b>'
        ];
        $parseOptions = array(
            'splitPattern' => '/(\[@)|\]/',
            'fieldPrefix' => '[@'
        );

        // Act
        $loader = new ArrayLoader($parseOptions);
        $parsed = $loader->load($templates);

        // Assert
        $this->assertInstanceOf(AirTemplate\Loader\ArrayLoader::class, $loader);

        $this->assertTrue(is_array($parsed));
        $this->assertEquals(1, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);
        $this->assertEquals(0, count($parsed['test']['options']['var1']));
        $this->assertEquals(1, count($parsed['test']['options']['var2']));
        $this->assertEquals('esc', $parsed['test']['options']['var2'][0]);
    }
}