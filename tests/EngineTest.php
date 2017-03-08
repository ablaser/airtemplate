<?php

require_once 'lib/MyClass.php';
require_once 'lib/MyTestView.php';
require_once 'lib/MyLib.php';

use PHPUnit\Framework\TestCase;
use AirTemplate\Loader\ArrayLoader;
use AirTemplate\Builder;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");


class DataObj {
    public $var1 = 'Hello';
    public $var2 = 'World';

    public function myDataMethod()
    {
        return __FUNCTION__ . ': abc';
    }
    public static function myDataStatic()
    {
        return __FUNCTION__ . ': abc';
    }
}

class EngineTest extends PHPUnit_Framework_TestCase
{

    public function testBaseEngineRenderArray()
    {

        $templates = [
            'test01' => '<b>{{var1}} {{var2|int|esc}}</b>',
            'test02' => '{{var1|md5}}',
            'test03' => '{{var1|sprintf("%1.6f", ?)|default("0")}}',
            'test04' => '{{item|escape}}',
            'test05' => '{{var1|md555}}',
            'test06' => '{{var1|sprintfff("%1.6f", ?)}}',

            'test11' => '{{var1|default("default-value")}}',
            'test12' => '{{var1|default}}',

            'test21' => '{{var1|user:myTestUserFunction}}',
            'test22' => '{{var1|app:myTestMethod}}',
            'test23' => '{{var1|app::myTestStatic}}',
            'test24' => '{{var1|\MyNamespace\MySubNamespace\MyClass::myStaticMethod}}',
            'test25' => '{{var1|xxx:myTestMethod}}',
        ];

        // Act
        $app = new MyTestView;
        $builder = new Builder(new ArrayLoader, $app);
        $engine = $builder->build($templates);

        // Assert
        $this->assertInstanceOf(AirTemplate\BaseEngine::class, $engine);

        $test01 = $engine->render('test01', ['var1' => 'hello', 'var2' => 1]);
        $this->assertEquals('<b>hello 1</b>', $test01);
        $test01 = $engine->render('test01', ['var1' => 'hello', 'var2' => 12.34]);
        $this->assertEquals('<b>hello 12</b>', $test01);

        $test02 = $engine->render('test02', ['var1' => 'hello']);
        $this->assertEquals('5d41402abc4b2a76b9719d911017c592', $test02);

        $test03 = $engine->render('test03', ['var1' => pi()]);
        $this->assertEquals('3.141593', $test03);

        $test03 = $engine->each(
            'test03',
            [
                ['var1' => 123.4567890],
                ['var1' => 234.5678906]
            ],
            ' '
        );
        $this->assertEquals('123.456789 234.567891', $test03);

        $test04 = $engine->each(
            'test04',
            [
                'Hello',
                'adam & eve'
            ],
            ' '
        );
        $this->assertEquals('Hello adam &amp; eve', $test04);

        $test04 = $engine->each(
            'test04',
            'Hello adam & eve',
            ' '
        );
        $this->assertEquals('Hello adam &amp; eve', $test04);

        $rows = 0;
        $result = '';
        $addRow = function() use (&$rows, &$result){
            while (true) {
                $result .= (yield);
                $rows++;
            }
        };
        $engine->each(
            'test04',
            [
                'Hello',
                'adam & eve'
            ],
            ' ',
            $addRow()
        );
        $this->assertEquals('Hello adam &amp; eve', $result);
        $this->assertEquals(2, $rows);

        $test05 = $engine->render('test05', ['var1' => 'hello']);
        $this->assertEquals('hello', $test05);

        $test06 = $engine->render('test06', ['var1' => 123.456]);
        $this->assertEquals('123.456', $test06);


        $test11 = $engine->render('test11', []);
        $this->assertEquals('default-value', $test11);

        $test12 = $engine->render('test12', []);
        $this->assertEquals('', $test12);


        $test21 = $engine->render('test21', ['var1' => 'abc']);
        $this->assertEquals('myTestUserFunction: abc', $test21);

        $test22 = $engine->render('test22', ['var1' => 'abc']);
        $this->assertEquals('myTestMethod: abc', $test22);

        $test23 = $engine->render('test23', ['var1' => 'abc']);
        $this->assertEquals('myTestStatic: abc', $test23);

        $test24 = $engine->render('test24', ['var1' => 'abc']);
        $this->assertEquals('myStaticMethod: abc', $test24);

        $test25 = $engine->render('test25', ['var1' => 'abc']);
        $this->assertEquals('abc', $test25);


        // test with no app object set
        $builder = new Builder(new ArrayLoader);
        $engine = $builder->build($templates);

        $test22 = $engine->render('test22', ['var1' => 'abc']);
        $this->assertEquals('abc', $test22);

        $test23 = $engine->render('test23', ['var1' => 'abc']);
        $this->assertEquals('abc', $test23);
    }

    public function testEngineRenderArray()
    {

        $templates = [
            'test03' => '{{var1|sprintf("%1.6f", ?)}}',

            'test12' => '{{var1|render("test03")}}',
            'test13' => '{{var1|render("test03", ?)}}',
            'test14' => '{{var1|each("test03", " ")}}',
            'test15' => ' {{var1=sub1/sub12|esc}} {{var2}}',
            'test16' => ' {{var1=/var2/sub2/sub21}} {{var3}}',
            'test17' => ' {{var1=/var3/sub3/sub31}} ',
            'test18' => ' {{var1=/var3/sub3/sub31|default("hello")}} ',
            'test19' => '{{var1|render}}',

            'test21' => '{{var1=/var2/sub2/sub21|user:myTestUserFunction}}',
        ];

        // Act
        $app = new MyTestView;
        $builder = new Builder(new ArrayLoader, $app);
        $engine = $builder->build($templates);

        // Assert
        $this->assertInstanceOf(AirTemplate\Engine::class, $engine);

        $test12 = $engine->render('test12', ['var1' => ['var1' => pi()]]);
        $this->assertEquals('3.141593', $test12);

        $test13 = $engine->render('test13', ['var1' => pi()]);
        $this->assertEquals('3.141593', $test13);

        $test14 = $engine->render(
            'test14',
            ['var1' => [['var1' => 123.4567890], ['var1' => 234.5678906]]]
        );
        $this->assertEquals('123.456789 234.567891', $test14);

        $test15 = $engine->render('test15', [
            'var1' => [
                'sub1' => [
                    'sub11' => 'value sub 11',
                    'sub12' => 'value sub 12',
                ]
            ],
            'var2' => 'abc'
        ]);
        $this->assertEquals(' value sub 12 abc', $test15);

        $test16 = $engine->render('test16', [
            'var2' => [
                'sub2' => [
                    'sub21' => 'value sub 21',
                    'sub22' => 'value sub 22',
                ]
            ]
        ]);
        $this->assertEquals(' value sub 21 ', $test16);

        $test17 = $engine->render('test17', []);
        $this->assertEquals('  ', $test17);

        $test18 = $engine->render('test18', []);
        $this->assertEquals(' hello ', $test18);

        $test19 = $engine->render('test19', []);
        $this->assertEquals('', $test19);


        $test21 = $engine->render('test21', [
            'var2' => [
                'sub2' => [
                    'sub21' => 'value sub 21',
                    'sub22' => 'value sub 22',
                ]
            ]
        ]);
        $this->assertEquals('myTestUserFunction: value sub 21', $test21);
    }

    public function testRenderObject()
    {

        $templates = [
            'test01' => '<b>{{var1}} {{var2|int|esc}}</b>',
            'test02' => '{{var1|md5}}',
            'test03' => '{{var1|sprintf("%1.6f", ?)}}',
            'test04' => '{{item|escape}}',
            'test05' => '{{var1|md555}}',
            'test06' => '{{var1|sprintfff("%1.6f", ?)}}',

            'test11' => '{{var1|default("default-value")}}',
            'test12' => '{{var1|default}}',

            'test21' => '{{var1|user:myTestUserFunction}}',
            'test22' => '{{var1|app:myTestMethod}}',
            'test23' => '{{var1|app::myTestStatic}}',
            'test24' => '{{var1|\MyNamespace\MySubNamespace\MyClass::myStaticMethod}}',
            'test25' => '{{var1|xxx:myTestMethod}}',

            'test31' => '{{var1|data:myDataMethod}}',
            'test32' => '{{var1|data::myDataStatic}}',
        ];

        // Act
        $app = new MyTestView;
        $builder = new Builder(new ArrayLoader, $app);
        $engine = $builder->build($templates);

        // Assert
        $this->assertInstanceOf(AirTemplate\BaseEngine::class, $engine);

        $test01 = $engine->render('test01', (object) ['var1' => 'hello', 'var2' => 1]);
        $this->assertEquals('<b>hello 1</b>', $test01);
        $test01 = $engine->render('test01', (object) ['var1' => 'hello', 'var2' => 12.34]);
        $this->assertEquals('<b>hello 12</b>', $test01);

        $test02 = $engine->render('test02', (object) ['var1' => 'hello']);
        $this->assertEquals('5d41402abc4b2a76b9719d911017c592', $test02);

        $test03 = $engine->render('test03', (object) ['var1' => pi()]);
        $this->assertEquals('3.141593', $test03);

        $test03 = $engine->each(
            'test03',
            (object) [
                ['var1' => 123.4567890],
                ['var1' => 234.5678906]
            ],
            ' '
        );
        $this->assertEquals('123.456789 234.567891', $test03);

        $test04 = $engine->each(
            'test04',
            (object) [
                'Hello',
                'adam & eve'
            ],
            ' '
        );
        $this->assertEquals('Hello adam &amp; eve', $test04);

        $rows = 0;
        $result = '';
        $addRow = function() use (&$rows, &$result){
            while (true) {
                $result .= (yield);
                $rows++;
            }
        };
        $engine->each(
            'test04',
            (object) [
                'Hello',
                'adam & eve'
            ],
            ' ',
            $addRow()
        );
        $this->assertEquals('Hello adam &amp; eve', $result);
        $this->assertEquals(2, $rows);

        $test05 = $engine->render('test05', (object) ['var1' => 'hello']);
        $this->assertEquals('hello', $test05);

        $test06 = $engine->render('test06', (object) ['var1' => 123.456]);
        $this->assertEquals('123.456', $test06);


        $test11 = $engine->render('test11', (object) []);
        $this->assertEquals('default-value', $test11);

        $test12 = $engine->render('test12', (object) []);
        $this->assertEquals('', $test12);


        $test21 = $engine->render('test21', (object) ['var1' => 'abc']);
        $this->assertEquals('myTestUserFunction: abc', $test21);

        $test22 = $engine->render('test22', (object) ['var1' => 'abc']);
        $this->assertEquals('myTestMethod: abc', $test22);

        $test23 = $engine->render('test23', (object) ['var1' => 'abc']);
        $this->assertEquals('myTestStatic: abc', $test23);

        $test24 = $engine->render('test24', (object) ['var1' => 'abc']);
        $this->assertEquals('myStaticMethod: abc', $test24);

        $test25 = $engine->render('test25', (object) ['var1' => 'abc']);
        $this->assertEquals('abc', $test25);


        $dataObj = json_decode(json_encode([
            'colors' => [
                'red',
                'green',
                'blue'
            ]
        ], JSON_FORCE_OBJECT));
        $this->assertEquals(
            'red-green-blue',
            $engine->each('test04', $dataObj->colors, '-')
        );

        $dataObj = json_decode(json_encode([
            'colors' => [
                'red',
                'green',
                'blue'
            ]
        ]));
        $this->assertEquals(
            'red-green-blue',
            $engine->each('test04', $dataObj->colors, '-')
        );

        $dataObj = new DataObj;
        $this->assertEquals(
            'myDataMethod: abc',
            $engine->render('test31', $dataObj)
        );
        $this->assertEquals(
            'myDataStatic: abc',
            $engine->render('test32', $dataObj)
        );


        // test with no app object set
        $builder = new Builder(new ArrayLoader);
        $engine = $builder->build($templates);

        $test22 = $engine->render('test22', (object) ['var1' => 'abc']);
        $this->assertEquals('abc', $test22);

        $test23 = $engine->render('test23', (object) ['var1' => 'abc']);
        $this->assertEquals('abc', $test23);
    }

    public function testEngineRenderObject()
    {
        $templates = [
            'test03' => '{{var1|sprintf("%1.6f", ?)}}',

            'test12' => '{{var1|render("test03")}}',
            'test13' => '{{var1|render("test03", ?)}}',
            'test14' => '{{var1|each("test03", " ")}}',
            'test15' => ' {{var1=sub1/sub12|esc}} {{var2}}',
            'test16' => ' {{var1=/var2/sub2/sub21}} {{var3}}',
            'test17' => ' {{var1=/var3/sub3/sub31}} ',
            'test18' => ' {{var1=/var3/sub3/sub31|default("hello")}} ',
            'test19' => '{{var1|render}}',

            'test21' => '{{var1=/var2/sub2/sub21|user:myTestUserFunction}}',
        ];

        $templates2 = [
            'test41' => '{{var1=/A/@status}}',
            'test42' => '{{A=B/@data-xy}}'
        ];

        // Act
        $app = new MyTestView;
        $builder = new Builder(new ArrayLoader, $app);
        $engine = $builder->build($templates);

        // Assert
        $this->assertInstanceOf(AirTemplate\Engine::class, $engine);

        $test12 = $engine->render('test12', (object) ['var1' => (object) ['var1' => pi()]]);
        $this->assertEquals('3.141593', $test12);

        $test13 = $engine->render('test13', (object) ['var1' => pi()]);
        $this->assertEquals('3.141593', $test13);

        $test14 = $engine->render(
            'test14',
            (object) ['var1' => [
                (object) ['var1' => 123.4567890],
                (object) ['var1' => 234.5678906]
            ]]
        );
        $this->assertEquals('123.456789 234.567891', $test14);

        $test15 = $engine->render('test15', (object) [
            'var1' => (object) [
                'sub1' => (object) [
                    'sub11' => 'value sub 11',
                    'sub12' => 'value sub 12',
                ]
            ],
            'var2' => 'abc'
        ]);
        $this->assertEquals(' value sub 12 abc', $test15);

        $test16 = $engine->render('test16', (object) [
            'var2' => (object) [
                'sub2' => (object) [
                    'sub21' => 'value sub 21',
                    'sub22' => 'value sub 22',
                ]
            ]
        ]);
        $this->assertEquals(' value sub 21 ', $test16);

        $test17 = $engine->render('test17', (object) []);
        $this->assertEquals('  ', $test17);

        $test18 = $engine->render('test18', (object) []);
        $this->assertEquals(' hello ', $test18);

        $test19 = $engine->render('test19', (object) []);
        $this->assertEquals('', $test19);


        $test21 = $engine->render('test21', (object) [
            'var2' => (object) [
                'sub2' => (object) [
                    'sub21' => 'value sub 21',
                    'sub22' => 'value sub 22',
                ]
            ]
        ]);
        $this->assertEquals('myTestUserFunction: value sub 21', $test21);


        $engine = $builder->build($templates2);
        $xmlstr = '<?xml version="1.0" encoding="utf-8"?>
<xmltest>
<A status="value">
<B data-xy="xyz">
<C></C>
</B>
</A>
</xmltest>';
        $simplexmlobj = simplexml_load_string($xmlstr);

        $test41 = $engine->render('test41', $simplexmlobj);
        $this->assertEquals('value', $test41);

        $test41 = $engine->render('test41', (object) []);
        $this->assertEquals('', $test41);

        $test42 = $engine->render('test42', $simplexmlobj);
        $this->assertEquals('xyz', $test42);
    }

    public function testRenderException()
    {

        $templates = [
            'test01' => '<b>{{var1}} {{var2|int|esc}}</b>',
        ];

        $this->setExpectedException(RuntimeException::class);

        $builder = new Builder(new ArrayLoader);
        $engine = $builder->build($templates);
        $engine->render('test-404');
    }

    public function testEachException()
    {

        $templates = [
            'test01' => '<b>{{var1}} {{var2|int|esc}}</b>',
        ];

        $this->setExpectedException(RuntimeException::class);

        $builder = new Builder(new ArrayLoader);
        $engine = $builder->build($templates);
        $engine->each(
            'test-404',
            [
                [ 'var1' => 'hello' ],
                [ 'var1' => 'world' ]
            ]
        );
    }
}