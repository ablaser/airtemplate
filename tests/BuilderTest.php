<?php

require_once 'lib/MyClass.php';
require_once 'lib/MyTestView.php';

use PHPUnit\Framework\TestCase;
use AirTemplate\Loader\ArrayLoader;
use AirTemplate\Builder;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");


class BuilderTest extends PHPUnit_Framework_TestCase
{

    public function testBuilder()
    {

        $templates = [
            'test01' => '<b>{{var1}} {{var2|int|esc}}</b>',
            'test02' => '{{var1|md5}}',
            'test03' => '{{var1|sprintf("%1.6f", ?)}}',

            'test11' => '{{var1|render("test2")}}',
            'test12' => '{{var1|render("test2", ?)}}',
            'test13' => '{{var1|each("test2")}}',
            'test14' => '{{var1|default("default-value")}}',

            'test21' => '{{var1|user:myTestUserFunction}}',
            'test22' => '{{var1|app:myTestMethod}}',
            'test23' => '{{var1|app::myTestStatic}}',
            'test24' => '{{var1|\MyNamespace\MySubNamespace\MyClass::myStaticMethod}}',
        ];

        // Act
        $app = new MyTestView;
        $builder = new Builder(new ArrayLoader, $app);
        $engine = $builder->build($templates);
        $parsed = $builder->getTemplates();
        $options = $builder->getFieldOptions();

        // Assert
        $this->assertInstanceOf(AirTemplate\Builder::class, $builder);

        $this->assertEquals(true, is_array($parsed));
        $this->assertEquals(11, count($parsed));
        $this->assertEquals(5, count($parsed['test01']['template']));
        $this->assertEquals(2, count($parsed['test01']['fields']));
        $this->assertEquals('var2', $parsed['test01']['fields'][3]);

        $this->assertEquals(0, count($parsed['test01']['options']['var1']));

        $this->assertEquals(2, count($parsed['test01']['options']['var2']));
        $this->assertEquals('int', $parsed['test01']['options']['var2'][0]);
        $this->assertEquals('esc', $parsed['test01']['options']['var2'][1]);

        $this->assertEquals(1, count($parsed['test02']['options']['var1']));
        $this->assertEquals('md5', $parsed['test02']['options']['var1'][0]);

        $this->assertEquals('sprintf', $parsed['test03']['options']['var1'][0][0]);
        $this->assertEquals('%1.6f', $parsed['test03']['options']['var1'][0][1][0]);

        $this->assertEquals('php:', $options['test01']['var2'][0][0]);
        $this->assertEquals('intval', $options['test01']['var2'][0][1]);
        $this->assertEquals('htmlspecialchars', $options['test01']['var2'][1][1]);

        $this->assertEquals('md5', $options['test02']['var1'][0][1]);

        $this->assertEquals('sprintf', $options['test03']['var1'][0][1][0]);
        $this->assertEquals('%1.6f', $options['test03']['var1'][0][1][1][0]);
        $this->assertEquals(' ?', $options['test03']['var1'][0][1][1][1]);

        $this->assertEquals('self:', $options['test11']['var1'][0][0]);
        $this->assertEquals('render', $options['test11']['var1'][0][1][0]);
        $this->assertEquals('test2', $options['test11']['var1'][0][1][1][0]);

        $this->assertEquals('render', $options['test12']['var1'][0][1][0]);
        $this->assertEquals('test2', $options['test12']['var1'][0][1][1][0]);
        $this->assertEquals(' ?', $options['test12']['var1'][0][1][1][1]);

        $this->assertEquals('each', $options['test13']['var1'][0][1][0]);
        $this->assertEquals('test2', $options['test13']['var1'][0][1][1][0]);

        $this->assertEquals('default:', $options['test14']['var1'][0][0]);
        $this->assertEquals('default-value', $options['test14']['var1'][0][1]);

        $this->assertEquals('user:', $options['test21']['var1'][0][0]);
        $this->assertEquals('myTestUserFunction', $options['test21']['var1'][0][1]);

        $this->assertInstanceOf(MyTestView::class, $options['test22']['var1'][0][0]);
        $this->assertEquals('myTestMethod', $options['test22']['var1'][0][1]);

        $this->assertEquals('MyTestView', $options['test23']['var1'][0][0]);
        $this->assertEquals('myTestStatic', $options['test23']['var1'][0][1]);

        $this->assertEquals('\MyNamespace\MySubNamespace\MyClass', $options['test24']['var1'][0][0]);
        $this->assertEquals('myStaticMethod', $options['test24']['var1'][0][1]);
    }

    public function testArrayLoaderCustomDelim()
    {

        $templates = [
            'test' => '<b>[@var1] [@var2|int|esc]</b>'
        ];
        $parseOptions = array(
            'splitPattern' => '/(\[@)|\]/',
            'fieldPrefix' => '[@'
        );

        // Act
        $builder = new Builder;
        $builder->setLoader(new ArrayLoader($parseOptions));
        $engine = $builder->build($templates);
        $parsed = $builder->getTemplates();

        // Assert
        $this->assertEquals(true, is_array($parsed));
        $this->assertEquals(1, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);

        $this->assertEquals(0, count($parsed['test']['options']['var1']));

        $this->assertEquals(2, count($parsed['test']['options']['var2']));
        $this->assertEquals('int', $parsed['test']['options']['var2'][0]);
        $this->assertEquals('esc', $parsed['test']['options']['var2'][1]);
    }
}