<?php

use PHPUnit\Framework\TestCase;

use AirTemplate\Template;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");

class TemplateTest extends TestCase
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


	public function testRender()
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
		// arguments 1, 2 + 3 (options): apply built-in htmlspecialchars()
		$this->assertEquals(
			'<b>a&amp;b</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'a&b' ],
				[ 'var1' => 'htmlspecialchars' ]
			)
		);
		// arguments 1, 2 + 3 (options): apply built-in htmlspecialchars()
		$this->assertEquals(
			'<b>a&amp;b</b>',
			$engine->render(
				'test-3',
				[ 'var1' => 'a&b' ],
				[ 'var1' =>
					[
						['htmlspecialchars', '{value}', ENT_HTML5]
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply built-in urlencode()
		$this->assertEquals(
			'foo+%40%2B%25%2F',
			$engine->render(
				'test-4',
				[ 'var1' => 'foo @+%/' ],
				[ 'var1' => 'urlencode' ]
			)
		);
		// arguments 1, 2 + 3 (options): apply built-in urlencode()
		$this->assertEquals(
			'foo%20%40%2B%25%2F',
			$engine->render(
				'test-4',
				[ 'var1' => 'foo @+%/' ],
				[ 'var1' => 'rawurlencode' ]
			)
		);
		// arguments 1, 2 + 3 (options): apply sprintf
		//$value = '{value}';
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
		// arguments 1, 2 + 3 (options): apply sprintf and htmlspecialchars result
		$this->assertEquals(
			'<b>& 3.141593</b>',
			$engine->render(
				'test-3',
				[ 'var1' => pi() ],
				[ 'var1' =>
					[
						['sprintf', '& %1.6f', '{value}']
					]
				]
			)
		);
		$this->assertEquals(
			'<b>&amp; 3.141593</b>',
			$engine->render(
				'test-3',
				[ 'var1' => pi() ],
				[ 'var1' =>
					[
						['sprintf', '& %1.6f', '{value}'],
						'htmlspecialchars'
					]
				]
			)
		);
		// arguments 1, 2 + 3 (options): apply function() (closure)
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
		// arguments 1, 2 + 3 (options): apply function() (closure)
		$closure1 = function($value, $field, &$data, $is_obj)
					{
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
		//$testhelper = new testhelper;
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
		// arguments 1, 2 + 3 (options): apply function
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

	// used in testRender
	public function render_abc($value, $field, &$data, $is_obj)
	{
		return '*' . $value . '*';
	}

	public function testEach()
	{

		$templates = array(
			'test-1' => '<b>{{var1}}</b>',
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

		// generator mode
		$result = '';
		$rows = 0;
		$addRow = function() use (&$rows, &$result){
			while (true) {
				$result .= yield;
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
		$this->assertEquals(2, $rows);
		$this->assertEquals('<b>hello</b> <b>adam &amp; eve</b>', $result);
		$this->assertEquals(2, $rows2);
		$this->assertEquals('<b>hello</b> <b>adam &amp; eve</b>', $result2);

	}

	// used in testEach
	private function addRow(&$rows, &$result) {
		while (true) {
			$result .= yield;
			$rows++;
		}
	}

}

// used in testRender
function render_abc($value)
{
	return '*' . $value . '*';
}
