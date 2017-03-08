<?php

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Gamez\Psr\Log\TestLoggerTrait;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use AirTemplate\Loader\CacheLoader;

if (!defined('n')) define('n', "\n");
if (!defined('br')) define('br', "<br>");

class CacheLoaderTest extends PHPUnit_Framework_TestCase
{
    use TestLoggerTrait;

    protected $logger;

    private $templates = array(
        'test' => '<b>{{var1}} {{var2|esc}}</b>',
    );

    private $custom_delim = array(
        'test' => '<b>[@var1] [@var2|esc]</b>'
    );

    protected function setUp()
    {
        $this->root = vfsStream::setup('test');

        $this->cache = vfsStream::url('test/cache');

        $this->file_1 = vfsStream::url('test/test_1.tmpl');
        file_put_contents($this->file_1, $this->templates['test']);

        $this->file_2 = vfsStream::url('test/test_2.tmpl');
        file_put_contents($this->file_2, $this->custom_delim['test']);

        $this->logger = $this->getTestLogger();
    }

    public function testCacheLoader()
    {

        // Act
        $cache = new FilesystemAdapter('', 60, $this->cache);
        $loader = new CacheLoader($cache, 60, $this->root->url());
        // cache templates
        $parsed = $loader->load(['test' => 'test_1.tmpl']);
        // read cached templates
        $parsed2 = $loader->load(['test' => 'test_1.tmpl']);

        // Assert
        $this->assertInstanceOf(AirTemplate\Loader\CacheLoader::class, $loader);

        $this->assertTrue(is_array($parsed));

        $this->assertTrue(is_array($parsed2));
        $this->assertEquals($parsed, $parsed2);

        $this->assertEquals(1, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);
        $this->assertEquals(0, count($parsed['test']['options']['var1']));
        $this->assertEquals(1, count($parsed['test']['options']['var2']));
        $this->assertEquals('esc', $parsed['test']['options']['var2'][0]);
    }

    public function testCacheLoaderCustomDelim()
    {

        $parseOptions = array(
            'splitPattern' => '/(\[@)|\]/',
            'fieldPrefix' => '[@'
        );

        // Act
        $cache = new FilesystemAdapter('', 60, $this->cache);
        $cache->clear();
        $loader = new CacheLoader($cache, 60, $this->root->url(), $parseOptions);
        // chache templates
        $parsed = $loader->load(['test' => 'test_2.tmpl']);
        // read cached templates
        $parsed2 = $loader->load(['test' => 'test_2.tmpl']);

        // Assert
        $this->assertTrue(is_array($parsed));

        $this->assertTrue(is_array($parsed2));
        $this->assertEquals($parsed, $parsed2);

        $this->assertEquals(1, count($parsed));
        $this->assertEquals(5, count($parsed['test']['template']));
        $this->assertEquals(2, count($parsed['test']['fields']));
        $this->assertEquals('var2', $parsed['test']['fields'][3]);
        $this->assertEquals(0, count($parsed['test']['options']['var1']));
        $this->assertEquals(1, count($parsed['test']['options']['var2']));
        $this->assertEquals('esc', $parsed['test']['options']['var2'][0]);
    }

    public function testLogger()
    {
        // Act
        $cache = new FilesystemAdapter('', 60, $this->cache);
        $cache->clear();
        $loader = new CacheLoader($cache, 60);
        $loader->setDir($this->root->url());
        $loader->setLogger($this->logger);
        // chache templates
        $parsed = $loader->load(['test' => 'test_2.tmpl']);
        // read cached templates
        $parsed2 = $loader->load(['test' => 'test_2.tmpl']);

        // Assert
        $this->assertTrue(is_array($parsed));
        $this->assertEquals(1, count($parsed));
        $this->assertTrue($this->logger->hasRecord('Cache hit: '));
        $this->assertTrue($this->logger->hasRecord('Cache save: '));

    }
}