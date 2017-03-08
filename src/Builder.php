<?php
/**
 * This file is part of AirTemplate.
 *
 * (c) 2016 Andreas Blaser
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package AirTemplate
 * @author  Andreas Blaser <a.blaser.66@gmail.com>
 * @license http://www.spdx.org/licenses/MIT MIT License
 */

namespace AirTemplate;

use AirTemplate\Loader\LoaderInterface;

/**
 * Class Builder
 *
 * The engine builder class.
 */
class Builder
{

    /**
     * Template loader object.
     *
     * @see Template::__construct()
     *
     * @var object
     */
    protected $loader = null;

    /**
     * App object.
     *
     * @see Template::__construct()
     *
     * @var object
     */
    protected $app = null;

    /**
     * Classname of $app.
     *
     * @var string
     */
    protected $appclass = '';

    /**
     * Array of parsed templates.
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Array of field options specifioed in templates
     *
     * @var array
     */
    protected $fieldOptions = [];

    /**
     * Complexity level (0=low, 1=high)
     *
     * @var int
     */
    protected $complexity = 0;

    /**
     * Shortcuts of php functions.
     *
     * @var array
     */
    private $phpShortcuts = [
        'esc' => 'htmlspecialchars',
        'escape' => 'htmlspecialchars',
        'urlenc' => 'urlencode',
        'rawurlenc' => 'rawurlencode',
        'ucase' => 'strtoupper',
        'lcase' => 'strtolower',
        'int' => 'intval',
        'float' => 'floatval',
    ];

    /**
     * Array of parse options.
     *
     * @see Template::__construct()
     *
     * @var array
     */
    protected $parseOptions = [
        'splitPattern' => '/(\{\{)|\}\}/',
        'fieldPrefix' => '{{'
    ];


    /**
     * Constructor.
     *
     * @param LoaderInterface $loader Template parser options
     * @param object          $app    App object
     */
    public function __construct(LoaderInterface $loader = null, $app = null)
    {
        $this->setLoader($loader);
        $this->setApp($app);
    }

    /**
     * Creates a render engine loaded with templates.
     *
     * @param string|array $templates Glob pattern or array of filenames
     *
     * @return Engine
     */
    public function build($templates = '')
    {
        if (!empty($templates)) {
            $this->load($templates);
        }
        if ($this->complexity > 0) {
            return new \AirTemplate\Engine($this->templates, $this->fieldOptions);
        }
        return new \AirTemplate\BaseEngine($this->templates, $this->fieldOptions);
    }

    /**
     * Loads and parses templates and create field options array.
     *
     * @param string|array $templates Glob pattern or array of filenames
     *
     * @return void
     */
    public function load($templates)
    {
        $this->complexity = 0;
        $this->templates = $this->loader->load($templates);
        $this->createFieldOptions();
        if ($this->complexity == 0) {
            foreach ($this->templates as $name => $template) {
                if ($template['complexity'] > 0) {
                    $this->complexity = 1;
                    break;
                }
            }
        }
    }

    /**
     * Sets a new loader object.
     *
     * @param LoaderInterface $loader Template parser options
     *
     * @return Builder
     */
    public function setLoader(LoaderInterface $loader = null)
    {
        $this->loader = $loader;
        return $this;
    }

    /**
     * Sets a new app object.
     *
     * @param object $app App object
     *
     * @return Builder
     */
    public function setApp($app = null)
    {
        $this->app = $app;
        if (!empty($app)) {
            $this->appclass = get_class($app);
        }
        return $this;
    }

    /**
     * Returns the internal templates array.
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Returns the internal field options array.
     *
     * @return array
     */
    public function getFieldOptions()
    {
        return $this->fieldOptions;
    }

    /**
     * Builds the field options array from the raw options.
     *
     * @return void
     */
    protected function createFieldOptions()
    {
        foreach ($this->templates as $name => $template) {
            foreach ($template['options'] as $field => $options) {
                $this->fieldOptions[$name][$field] = isset($options[0])
                    ? $this->convertFieldOptions($options)
                    : false;
            }
        }
    }

    /**
     * Convert the raw field option pipeline from the template
     * into "callables".
     *
     * @param array $options Array of options
     *
     * @return array Array of prepared options
     */
    protected function convertFieldOptions(array $options)
    {
        $opts = [];
        foreach ($options as $option) {
            $opt = $this->getCallback($option);
            if ($opt !== false) {
                $opts[] = $opt;
            }
        }
        return $opts;
    }

    /**
     * Convert a field option into internal callback format.
     *
     * @param array $option A single option
     *
     * @return mixed callback array or false
     */
    protected function getCallback($option)
    {
        if (is_array($option)) {
            return $this->getEngineCallback($option);
        }
        if (false !== $opt = $this->getAppCallback($option)) {
            return $opt;
        }
        if (isset($this->phpShortcuts[strtolower($option)])) {
            return ['php:', $this->phpShortcuts[strtolower($option)]];
        }
        if (is_string($option)) {
            return ['php:', $option];
        }
        return false;
    }

    /**
     * Convert a field option into internal callback format.
     *
     * @param array $option A single option
     *
     * @return callable|false
     */
    protected function getEngineCallback(array $option)
    {
        switch (strtolower($option[0])) {
            case 'render':
            case 'each':
                if (isset($option[1][0])) {
                    $this->complexity = 1;
                    return ['self:', $option];
                }
                return false;
            case 'default':
                if (isset($option[1][0])) {
                    return ['default:', $option[1][0]];
                }
                return false;
        }
        return ['php:', $option];
    }

    /**
     * Creates the app specific callback.
     *
     * @param string $option A single option
     *
     * @return callable|false
     */
    protected function getAppCallback($option)
    {
        $func = explode('::', $option);
        if (count($func) == 2) {
            return $this->getAppStaticCb($func);
        }
        $func = explode(':', $option);
        if (count($func) == 2) {
            return $this->getAppInstanceCb($func);
        }
        return false;
    }

    /**
     * Creates the app specific static callback.
     *
     * @param array $option A single option
     *
     * @return callable|false
     */
    protected function getAppStaticCb($option)
    {
        switch (strtolower($option[0])) {
            case 'app':
                if ($this->appclass != '') {
                    return [$this->appclass, $option[1]];
                }
                return false;
            case 'data':
                return ['data::', $option[1]];
        }
        return [$option[0], $option[1]];
    }

    /**
     * Creates the app specific instance callback.
     *
     * @param array $option A single option
     *
     * @return callable|false
     */
    protected function getAppInstanceCb($option)
    {
        switch (strtolower($option[0])) {
            case 'app':
                if (isset($this->app)) {
                    return [$this->app, $option[1]];
                }
                return false;
            case 'user':
            case 'data':
                return [strtolower($option[0]) . ':', $option[1]];
        }
        return false;
    }
}
