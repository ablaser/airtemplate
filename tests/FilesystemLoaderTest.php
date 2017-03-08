<?php

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Gamez\Psr\Log\TestLoggerTrait;
use AirTemplate\Loader\FilesystemLoader;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");

class FilesystemLoaderTest extends PHPUnit_Framework_TestCase
{
    use TestLoggerTrait;

    protected $logger;

    private $templates = array(
        'test' => '<b>{{var1}} {{var2|esc}}</b>',
    );

    private $custom_delim = array(
        'custom_delim' => '<b>[@var1] [@var2|esc]</b>'
    );

    protected function setUp()
    {
        $this->root = vfsStream::setup('test');

        $this->file_1 = vfsStream::url('test/test_1.tmpl');
        file_put_contents($this->file_1, $this->templates['test']);

        $this->file_2 = vfsStream::url('test/custom_delim.tmpl');
        file_put_contents($this->file_2, $this->custom_delim['custom_delim']);

        $this->logger = $this->getTestLogger();
    }

    public function testFilesystemLoader()
    {
        // Act
        // set template dir and load test_1
        $loader = new FilesystemLoader;
        $loader->setDir($this->root->url());
        $parsed = $loader->load(['test_1.tmpl']);

        // Assert
        $this->assertInstanceOf(AirTemplate\Loader\FilesystemLoader::class, $loader);

        $this->assertTrue(is_array($parsed));
        $this->assertEquals(1, count($parsed));
        $this->assertEquals(5, count($parsed['test_1']['template']));
        $this->assertEquals(2, count($parsed['test_1']['fields']));
        $this->assertEquals('var2', $parsed['test_1']['fields'][3]);
        $this->assertEquals(0, count($parsed['test_1']['options']['var1']));
        $this->assertEquals(1, count($parsed['test_1']['options']['var2']));
        $this->assertEquals('esc', $parsed['test_1']['options']['var2'][0]);
    }

    public function testFilesystemLoaderCustomDelim()
    {
        // custom field delimiters
        $parseOptions = array(
            'splitPattern' => '/(\[@)|\]/',
            'fieldPrefix' => '[@'
        );

        // Act
        // no template dir, use qualified filename & rename template to test
        $loader = new FilesystemLoader('', $parseOptions);
        $parsed = $loader->load(['test' => $this->file_2]);

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

    public function testFilesystemLoaderGlob()
    {
        // Act
        // set template dir and load all files starting with 'rss'
        $loader = new FilesystemLoader('examples/templates/rss');
        $parsed = $loader->load('rss*');

        // Assert
        $this->assertTrue(is_array($parsed));
        $this->assertEquals(2, count($parsed));
    }

    public function testLogger()
    {
        // Act
        // set template dir and load all files starting with 'rss'
        $loader = new FilesystemLoader('examples/templates/rss');
        $loader->setLogger($this->logger);
        $parsed = $loader->load(['rss.tmpl', 'rxx.tmpl']);

        // Assert
        $this->assertTrue(is_array($parsed));
        $this->assertEquals(1, count($parsed));
        $this->assertTrue($this->logger->hasRecord('Template loaded: '));
        $this->assertTrue($this->logger->hasRecord('Templates loaded: '));

        $result = $loader->load(['rxx.tmpl', 'rxx_list.tmpl']);
        $log = $this->logger->getRecords();

        $this->assertFalse($result);
        $this->assertEquals(4, count($log));
        $this->assertTrue($this->logger->hasRecord('Template not loaded: '));

        $this->assertFalse($this->logger->hasRecord('Does not exist.'));
    }
}