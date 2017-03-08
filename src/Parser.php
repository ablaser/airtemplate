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

/**
 * Class Parser
 *
 * The template parser class.
 */
class Parser implements ParserInterface
{

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
     * @param array  $parseOptions Template parser options
     */
    public function __construct(array $parseOptions = [])
    {
        if (!empty($parseOptions)) {
            $this->parseOptions = $parseOptions;
        }
    }

    /**
     * Parses an array of source templates into internal representation.
     *
     * @param array $templates Array of raw templates
     *
     * @return array           An array of parsed templates
     */
    public function parse(array $templates)
    {
        $parsedTemplates = [];
        foreach ($templates as $name => $srcTemplate) {
            $parsedTemplates[$name] = $this->parseTemplate($srcTemplate);
        }
        return $parsedTemplates;
    }

    /**
     * Parse (split) string template into an array.
     *
     * The items in this array are either strings (template code)
     * or an array with a single element, the field name.
     *
     * @param string $template The source template
     *
     * @return void
     */
    protected function parseTemplate($template)
    {
        $split = preg_split(
            $this->parseOptions['splitPattern'],
            $template,
            -1,
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        $lsplit = count($split);
        $pindex = 0;
        $tindex = 0;
        $template = [
            'template' => [],
            'fields' => [],
            'datapath' => [],
            'options' => [],
            'complexity' => 0
        ];
        while ($pindex < $lsplit) {
            if ($split[$pindex] == $this->parseOptions['fieldPrefix']) {
                $pindex++;
                $options = $this->parseFieldOptions($split[$pindex]);
                $field = trim(array_shift($options));
                $fieldname = explode('=', $field)[0];
                $datapath = self::getDataPath($field);
                $template['template'][$tindex] = '';
                $template['fields'][$tindex] = $fieldname;
                $template['datapath'][$fieldname] = $datapath;
                $template['options'][$fieldname] = $options;
                if ($datapath !== false) {
                    $template['complexity'] = 1;
                }
            } else {
                $template['template'][$tindex] = $split[$pindex];
            }
            $pindex++;
            $tindex++;
        }
        return $template;
    }

    /**
     * Parse field and return a raw field options array.
     *
     * @param string $field Field name
     *
     * @return array Field options.
     */
    protected function parseFieldOptions($field)
    {
        $parts = explode('|', $field);
        $out = [];
        foreach ($parts as $part) {
            if (trim($part) != '') {
                $part = explode('(', $part);
                if (count($part) > 1) {
                    $out[] = [
                        $part[0],
                        str_getcsv(rtrim(implode('(', array_slice($part, 1)), ')'))
                    ];
                    continue;
                }
                $out[] = $part[0];
            }
        }
        return $out;
    }

    /**
     * Parse field and return a datapath array or false if there
     * is no datapath.
     *
     * @param string $field Field name
     *
     * @return array|bool
     */
    protected static function getDataPath($field)
    {
        $parts = explode('=', $field);
        if (count($parts) > 1) {
            $path = explode('/', $parts[1]);
            if ($path[0] == '') {
                array_shift($path);
            } else {
                array_unshift($path, $parts[0]);
            }
            return $path;
        }
        return false;
    }
}
