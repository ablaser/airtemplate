<?php

use PHPUnit\Framework\TestCase;

use AirTemplate\Template;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");

class DataObj {
	public $var1 = 'hello';
	public $var2 = 'world';
}


class TemplateTest extends PHPUnit_Framework_TestCase
{

	public function testParse()
	{

		$templates_1 = array(
			'test-1' => '<b>{{var1}}</b>',
			'test-2' => '<b>{{var2}}</b>',
		);
		$templates_2 = array(
			'test-3' => '<b>{{var1}}</b>',
		);

		$templates_3 = array(
			'test-1' => '<b>[@var1]</b>',
		);
		$templateParseOptions = array(
			'splitPattern' => '/(\[@)|\]/',
			'fieldPrefix' => '[@'
		);

		$parsed_1 = array();
		$parsed_2 = array();
		$parsed_3 = array();

		// Act
		// create a template object and load source templates
		$engine_1 = new Template;
		$engine_1->setTemplates($templates_1);
		$engine_1->setTemplates($templates_2, true);
		// get the compiled templates
		$parsed_1 = $engine_1->getParsedTemplates();
		// create a template object and load compiled templates
		$engine_2 = new Template;
		$engine_2->setParsedTemplates($parsed_1);
		$parsed_2 = $engine_2->getParsedTemplates();
		// create a template object using custom field delimiters
		$engine_3 = new Template($templateParseOptions);
		$engine_3->setTemplates($templates_3);
		$parsed_3 = $engine_3->getParsedTemplates();

		// Assert
		$this->assertEquals(3, count($parsed_1));
		$this->assertEquals(3, count($parsed_1['test-1']));
		$this->assertEquals('<b>', $parsed_1['test-1'][0]);
		$this->assertEquals('var1', $parsed_1['test-1'][1][0]);
		$this->assertEquals('</b>', $parsed_1['test-1'][2]);
		$this->assertEquals(3, count($parsed_1['test-2']));
		$this->assertEquals('<b>', $parsed_1['test-2'][0]);
		$this->assertEquals('var2', $parsed_1['test-2'][1][0]);
		$this->assertEquals('</b>', $parsed_1['test-2'][2]);
		$this->assertEquals(3, count($parsed_1['test-3']));
		$this->assertEquals('<b>', $parsed_1['test-3'][0]);
		$this->assertEquals('var1', $parsed_1['test-3'][1][0]);
		$this->assertEquals('</b>', $parsed_1['test-3'][2]);

		$this->assertEquals(3, count($parsed_2));
		$this->assertEquals(3, count($parsed_2['test-1']));
		$this->assertEquals('<b>', $parsed_2['test-1'][0]);
		$this->assertEquals('var1', $parsed_2['test-1'][1][0]);
		$this->assertEquals('</b>', $parsed_2['test-1'][2]);
		$this->assertEquals(3, count($parsed_2['test-2']));
		$this->assertEquals('<b>', $parsed_2['test-2'][0]);
		$this->assertEquals('var2', $parsed_2['test-2'][1][0]);
		$this->assertEquals('</b>', $parsed_2['test-2'][2]);
		$this->assertEquals(3, count($parsed_2['test-3']));
		$this->assertEquals('<b>', $parsed_2['test-3'][0]);
		$this->assertEquals('var1', $parsed_2['test-3'][1][0]);
		$this->assertEquals('</b>', $parsed_2['test-3'][2]);

		$this->assertEquals(1, count($parsed_3));
		$this->assertEquals(3, count($parsed_3['test-1']));
		$this->assertEquals('<b>', $parsed_3['test-1'][0]);
		$this->assertEquals('var1', $parsed_3['test-1'][1][0]);
		$this->assertEquals('</b>', $parsed_3['test-1'][2]);
	}

	public function testParseExceptionSetTemplates()
    {
		$this->setExpectedException(InvalidArgumentException::class);

		$engine = new Template;

		// test wrong argument type
		$engine->setTemplates('');
    }

	public function testParseExceptionSetParsedTemplates()
    {
		$this->setExpectedException(InvalidArgumentException::class);

		$engine = new Template;

		// test wrong argument type
		$engine->setParsedTemplates('');
    }

	public function testRenderArray()
	{
		$templates = array(
			'test-1' => '<b>{{var1}} {{var1}}</b>',
			'test-2' => '<b>{{var1}} {{var2}}</b>',
			'test-3' => '<b>{{var1}}</b>',
			'test-4' => '{{var1}}',
		);

		$engine = new Template;
		$engine->setTemplates($templates);

		// arguments 1 + 2: replace values (1 field)
		$this->assertEquals(
			'<b>hello hello</b>',
			$engine->render(
				'test-1',
				[ 'var1' => 'hello' ]
			)
		);
		// arguments 1 + 2: replace values (2 fields)
		$this->assertEquals(
			'<b>hello world</b>',
			$engine->render(
				'test-2',
				[ 'var1' => 'hello', 'var2' => 'world' ]
			)
		);
		// arguments 1, 2 + 3 (options): apply htmlspecialchars
		$this->assertEquals(
			'<b>a&amp;b</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'a&b' ],
				[ 'var1' => 'htmlspecialchars' ]
			)
		);
		// arguments 1, 2 + 3 (options): apply htmlspecialchars with param
		$this->assertEquals(
			'<b>a&amp;b</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'a&b' ],
				[ 'var1' =>
					[
						['htmlspecialchars', Template::FIELD_VALUE, ENT_HTML5]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply sprintf
		$this->assertEquals(
			'<b>  abc</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ 'var1' =>
					[
						['sprintf', '%5s', Template::FIELD_VALUE]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply sprintf (wildcard)
		$this->assertEquals(
			'<b>  abc</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ '*' =>
					[
						['sprintf', '%5s', Template::FIELD_VALUE]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply 2 options
		$this->assertEquals(
			'<b>&amp; 3.141593</b>',
			$engine->render(
				'test-3',
				[ 'var1' => pi() ],
				[ 'var1' =>
					[
						['sprintf', '& %1.6f', Template::FIELD_VALUE],
						'htmlspecialchars'
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply function (closure)
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ 'var1' => function($value, $field, &$data, $is_obj)
					{
						return '*' . $value . '*';
					}
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply 2 functions (closure)
		$this->assertEquals(
			'<b>+*abc*+</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ 'var1' =>
					[
						function($value, $field, &$data, $is_obj) {
							return '*' . $value . '*';
						},
						function($value, $field, &$data, $is_obj) {
							return '+' . $value . '+';
						},
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply function (closure)
		$closure1 = function($value, $field, &$data, $is_obj) {
			return '*' . $value . '*';
		};
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ 'var1' => $closure1 ]
			)
		);
		// arguments 1, 2 + 3 (options): apply object method
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ 'var1' =>
					[
						[$this, 'render_abc' ]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply custom function
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'abc' ],
				[ 'var1' => 'render_abc' ]
			)
		);
		// arguments 1, 2 + 3: option only (data['var1'] not set)
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				[ ],
				[ 'var1' => function($value, $field, &$data, $is_obj)
					{
						return '*abc*';
					}
				]
			)
		);

	}

	public function testRenderObject()
	{
		$dataObj = new DataObj;

		$templates = array(
			'test-1' => '<b>{{var1}} {{var1}}</b>',
			'test-2' => '<b>{{var1}} {{var2}}</b>',
			'test-3' => '<b>{{var1}}</b>',
			'test-4' => '{{var1}}',
		);

		$engine = new Template;
		$engine->setTemplates($templates);

		// arguments 1 + 2: replace values (1 field)
		$this->assertEquals(
			'<b>hello hello</b>',
			$engine->render(
				'test-1',
				$dataObj
			)
		);
		// arguments 1 + 2: replace values (2 fields)
		$this->assertEquals(
			'<b>hello world</b>',
			$engine->render(
				'test-2',
				$dataObj
			)
		);
		// arguments 1, 2 + 3 (options): apply htmlspecialchars
		$dataObj->var1 = 'a&b';
		$this->assertEquals(
			'<b>a&amp;b</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' => 'htmlspecialchars' ]
			)
		);
		// arguments 1, 2 + 3 (options): apply htmlspecialchars with param
		$this->assertEquals(
			'<b>a&amp;b</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' =>
					[
						['htmlspecialchars', Template::FIELD_VALUE, ENT_HTML5]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply sprintf
		$dataObj->var1 = 'abc';
		$this->assertEquals(
			'<b>  abc</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' =>
					[
						['sprintf', '%5s', Template::FIELD_VALUE]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply sprintf (wildcard)
		$dataObj->var1 = 'abc';
		$this->assertEquals(
			'<b>  abc</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ '*' =>
					[
						['sprintf', '%5s', Template::FIELD_VALUE]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply 2 options
		$dataObj->var1 = pi();
		$this->assertEquals(
			'<b>&amp; 3.141593</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' =>
					[
						['sprintf', '& %1.6f', Template::FIELD_VALUE],
						'htmlspecialchars'
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply function (closure)
		$dataObj->var1 = 'abc';
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' => function($value, $field, &$data, $is_obj)
					{
						return '*' . $value . '*';
					}
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply function (closure)
		$closure1 = function($value, $field, &$data, $is_obj) {
			return '*' . $value . '*';
		};
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' => $closure1 ]
			)
		);
		// arguments 1, 2 + 3 (options): apply object method
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' =>
					[
						[$this, 'render_abc' ]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply custom function
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				$dataObj,
				[ 'var1' => 'render_abc' ]
			)
		);
		// arguments 1, 2 + 3: option only (data['var1'] not set)
		$obj = new stdClass;
		$this->assertEquals(
			'<b>*abc*</b>',
			$engine->render(
				'test-3',
				$obj,
				[ 'var1' => function($value, $field, &$data, $is_obj)
					{
						return '*abc*';
					}
				]
			)
		);

	}

	// used in testRender
	public function render_abc($value, $field, &$data, $is_obj)
	{
		return '*' . $value . '*';
	}

	public function testRenderException()
    {
		$this->setExpectedException(Exception::class);

		$engine = new Template;

		// test template not found
        $engine->render('test-404');
    }

	public function testEach()
	{
		$templates = array(
			'test-1' => '<b>{{var1}}</b>',
		);
		$templates2 = array(
			'test-1' => '<b>{{item}}</b>',
		);

		$engine = new Template;
		$engine->setTemplates($templates);

		// Act
		// without delimiter
		$a = $engine->each(
			'test-1',
			[
				[ 'var1' => 'hello' ],
				[ 'var1' => 'world' ]
			]
		);
		// including delimiter
		$b = $engine->each(
			'test-1',
			[
				[ 'var1' => 'hello' ],
				[ 'var1' => 'world' ]
			],
			[],
			"\n"
		);
		// apply htmlspecialchars
		$c = $engine->each(
			'test-1',
			[
				[ 'var1' => 'hello' ],
				[ 'var1' => 'adam & eve' ]
			],
			[
				'var1' => 'htmlspecialchars',
			],
			' '
		);

		$engine2 = new Template;
		$engine2->setTemplates($templates2);
		// without delimiter
		$a2 = $engine2->each(
			'test-1',
			['hello', 'world']
		);

		// generator mode
		$result = '';
		$rows = 0;
		$addRow = function() use (&$rows, &$result){
			while (true) {
				$result .= (yield);
				$rows++;
			}
		};
		$engine->each(
			'test-1',
			[
				[ 'var1' => 'hello' ],
				[ 'var1' => 'adam & eve' ]
			],
			[
				'var1' => 'htmlspecialchars',
			],
			' ',
			$addRow()
		);
		$result2 = '';
		$rows2 = 0;
		$engine->each(
			'test-1',
			[
				[ 'var1' => 'hello' ],
				[ 'var1' => 'adam & eve' ]
			],
			[
				'var1' => 'htmlspecialchars',
			],
			' ',
			$this->addRow($rows2, $result2)
		);

		// Assert
		$this->assertEquals('<b>hello</b><b>world</b>', $a);
		$this->assertEquals('<b>hello</b>' . n . '<b>world</b>', $b);
		$this->assertEquals('<b>hello</b> <b>adam &amp; eve</b>', $c);
		$this->assertEquals('<b>hello</b><b>world</b>', $a2);
		$this->assertEquals(2, $rows);
		$this->assertEquals('<b>hello</b> <b>adam &amp; eve</b>', $result);
		$this->assertEquals(2, $rows2);
		$this->assertEquals('<b>hello</b> <b>adam &amp; eve</b>', $result2);
	}

	public function testEachException()
    {
		$this->setExpectedException(Exception::class);

		$engine = new Template;

		// test template not found
        $engine->each(
            'test-404',
            [
                [ 'var1' => 'hello' ],
                [ 'var1' => 'adam & eve' ]
            ]
        );
    }

	// used in testEach
	private function addRow(&$rows, &$result) {
		while (true) {
			$result .= (yield);
			$rows++;
		}
	}

}

// used in testRender
function render_abc($value)
{
	return '*' . $value . '*';
}
