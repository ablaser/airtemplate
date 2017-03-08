<?php

use PHPUnit\Framework\TestCase;
use AirTemplate\Parser;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");

class ParserTest extends PHPUnit_Framework_TestCase
{

    public function testParser()
    {

        $templates = [
            'test' => '<b>{{var1}} {{var2|int|esc}}</b>',
            'test2' => '{{var1}}',
            'test3' => '<b>{{var1|sprintf("%1.6f", ?)}} {{var2}}</b>',
        ];

        // Act
        $parser = new Parser;
        $parsed = $parser->parse($templates);

        // Assert
        $this->assertInstanceOf(AirTemplate\Parser::class, $parser);

        $this->assertTrue(is_array($parsed));
        $this->assertEquals(3, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);

        $this->assertEquals(0, count($parsed['test']['options']['var1']));

        $this->assertEquals(2, count($parsed['test']['options']['var2']));
        $this->assertEquals('int', $parsed['test']['options']['var2'][0]);
        $this->assertEquals('esc', $parsed['test']['options']['var2'][1]);

        $this->assertEquals(1, count($parsed['test2']['template']));
        $this->assertEquals(1, count($parsed['test2']['fields']));
        $this->assertEquals('var1', $parsed['test2']['fields'][0]);

        $this->assertEquals('sprintf', $parsed['test3']['options']['var1'][0][0]);
        $this->assertEquals('%1.6f', $parsed['test3']['options']['var1'][0][1][0]);
    }

    public function testParserCustomDelim()
    {

        $templates = [
            'test' => '<b>[@var1] [@var2|int|esc]</b>',
            'test2' => '[@var1]',
            'test3' => '<b>[@var1|sprintf("%1.6f", ?)] [@var2]</b>',
        ];
        $parseOptions = array(
            'splitPattern' => '/(\[@)|\]/',
            'fieldPrefix' => '[@'
        );

        // Act
        $parser = new Parser($parseOptions);
        $parsed = $parser->parse($templates);

        // Assert
        $this->assertTrue(is_array($parsed));
        $this->assertEquals(3, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);

        $this->assertEquals(0, count($parsed['test']['options']['var1']));

        $this->assertEquals(2, count($parsed['test']['options']['var2']));
        $this->assertEquals('int', $parsed['test']['options']['var2'][0]);
        $this->assertEquals('esc', $parsed['test']['options']['var2'][1]);

        $this->assertEquals(1, count($parsed['test2']['template']));
        $this->assertEquals(1, count($parsed['test2']['fields']));
        $this->assertEquals('var1', $parsed['test2']['fields'][0]);

        $this->assertEquals('sprintf', $parsed['test3']['options']['var1'][0][0]);
        $this->assertEquals('%1.6f', $parsed['test3']['options']['var1'][0][1][0]);
    }
}