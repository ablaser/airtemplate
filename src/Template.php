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
 * Class Template
 *
 * The engine class.
 */
class Template
{

    /**
     * String constant used for parameter replacement in field function calls.
     */
    const FIELD_VALUE = '$${v}$$';

    /**
     * Array of parsed templates.
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Array of parse options.
     *
     * @see Template::__construct()
     *
     * @var array
     */
    protected $options = [];

    /**
     * Data array or object.
     *
     * @var array|object
     */
    private $fieldData;

    /**
     * Field options array.
     *
     * @var array
     */
    private $fieldOptions = [];

    /**
     * Constructor.
     *
     * @param array $options Template parser options
     */
    public function __construct($options = [])
    {
        $this->options = [
            'splitPattern' => '/(\{\{)|\}\}/',
            'fieldPrefix' => '{{'
        ];
        if (!empty($options)) {
            $this->options = $options;
        }
    }

    /**
     * Renders the template $name using the values in $data. Optionally
     * apply specified field rendering options.
     *
     * @param string       $name    Template name
     * @param array|object $data    Replacement values
     * @param array        $options Field rendering options
     *
     * @return string      The rendered output
     */
    public function render($name, $data = [], $options = [])
    {
        if (!isset($this->templates[$name])) {
            throw new \Exception('Template "' . $name . '" does not exist.');
        }
        $this->fieldData = $data;
        $this->fieldOptions = $options;
        return $this->merge($name);
    }

    /**
     * Repeats the template for each item in $data and return the rendered
     * result. Optionally apply specified field rendering options.
     * If a function is given in $rowGenerator, each will send each rendered
     * row (one by one) to the rowGenerator function.
     * There is no return value in this case.
     *
     * @param string $name         Template name
     * @param array  $data         Simple array or 2-dim assoc. array
     * @param array  $options      Field rendering options
     * @param string $separator    Optional separator between items
     * @param object $rowGenerator A row generator function or null
     *
     * @return string|void         The rendered output or nothing in generator mode
     */
    public function each(
        $name,
        $data,
        $options = [],
        $separator = '',
        $rowGenerator = null
    ) {
        if (!isset($this->templates[$name])) {
            throw new \Exception('Template "' . $name . '" does not exist.');
        }
        $this->fieldOptions = $options;
        $i = 0;
        $buffer = '';
        foreach ($data as $row) {
            if (!is_array($row)) {
                $row = ['item' => $row];
            }
            $this->fieldData = $row;
            $temp = ($i > 0 ? $separator : '') . $this->merge($name);
            if (!isset($rowGenerator)) {
                $buffer .= $temp;
            } else {
                $rowGenerator->send($temp);
            }
            $i++;
        }
        if (!isset($rowGenerator)) {
            return $buffer;
        }
    }

    /**
     * Parses an array of raw templates and cache it in the global
     * templates array.
     *
     * @param array $sourceTemplates Assoc. array of raw templates
     * @param bool  $addTemplates    Add templates if true, replace otherwise
     *
     * @return Template              The object instance
     */
    public function setTemplates($sourceTemplates, $addTemplates = false)
    {
        if (!is_array($sourceTemplates)) {
            throw new \InvalidArgumentException(
                'Argument is not a valid templates array.'
            );
        }
        return $this->setParsedTemplates(
            $this->parseTemplates($sourceTemplates),
            $addTemplates
        );
    }

    /**
     * Set the global templates array.
     *
     * @param array $templates    Assoc. array of compiled templates
     * @param bool  $addTemplates Add templates if true, replace otherwise
     *
     * @return Template           The object instance
     */
    public function setParsedTemplates($templates, $addTemplates = false)
    {
        if (!is_array($templates)) {
            throw new \InvalidArgumentException(
                'Argument is not a valid templates array.'
            );
        }
        $this->templates = ($addTemplates)
            ? array_merge($this->templates, $templates)
            : $templates;
        return $this;
    }

    /**
     * Returns the templates array.
     *
     * @return array The templates array
     */
    public function getParsedTemplates()
    {
        return $this->templates;
    }

    /**
     * Parse (split) string templates into arrays.
     *
     * The items in this array are either strings (template code)
     * or an array with a single element, the field name.
     *
     * @param array $sourceTemplates The source templates array
     *
     * @return array                 The parsed templates array
     */
    protected function parseTemplates($sourceTemplates)
    {
        $templates = [];
        foreach ($sourceTemplates as $name => $template) {
            $split = preg_split(
                $this->options['splitPattern'],
                $template,
                -1,
                PREG_SPLIT_DELIM_CAPTURE
            );
            $lsplit = count($split);
            $i = 0;
            $tmpl = [];
            while ($i < $lsplit) {
                if ($split[$i] == $this->options['fieldPrefix']) {
                    $tmpl[] = [trim($split[$i + 1])];
                    $i += 2;
                } else {
                    $tmpl[] = $split[$i];
                    $i++;
                }
            }
            $templates[$name] = $tmpl;
        }
        return $templates;
    }

    /**
     * Merge template and values and return the rendered string. Replacement
     * values may be specified as an assoc array or an object.
     *
     * @param string $name Template name
     *
     * @return string      The rendered result
     */
    protected function merge($name)
    {
        $isObject = is_object($this->fieldData);
        $result = '';
        foreach ($this->templates[$name] as $part) {
            if (!is_array($part)) {
                $result .= $part;
                continue;
            }
            $field = $part[0];
            $value = self::getValue($field, $isObject);
            $option = self::getOption($field);
            if (empty($option)) {
                $result .= $value;
                continue;
            }
            $result .= $this->renderField(
                $value,
                $field,
                $option,
                $isObject
            );
        }
        return $result;
    }

    /**
     * get the value for a field or an empty string.
     *
     * @param string       $field    Field name
     * @param bool         $isObject Set to true, if $data is an object
     *
     * @return mixed       Field value or empty string
     */
    protected function getValue($field, $isObject)
    {
        if ($isObject) {
            return isset($this->fieldData->$field) ? $this->fieldData->$field : '';
        }
        return isset($this->fieldData[$field]) ? $this->fieldData[$field] : '';
    }

    /**
     * get option for a field.
     *
     * @param string $field Field name
     *
     * @return array        Selected option or empty string
     */
    protected function getOption($field)
    {
        if (isset($this->fieldOptions[$field])) {
            $option = $this->fieldOptions[$field];
        } elseif (isset($this->fieldOptions['*'])) {
            $option = $this->fieldOptions['*'];
        } else {
            return [];
        }
        return !is_array($option) ? (array) $option : $option;
    }

    /**
     * Renders the output according to the specified option.
     *
     * @see readme.md
     *
     * @param string $value    Replacement value
     * @param string $field    Field name
     * @param array  $options  Options for this field
     * @param bool   $isObject Set to true, if $data is an object
     *
     * @return string          The formatted value
     */
    protected function renderField(
        $value,
        $field,
        $options,
        $isObject
    ) {
        foreach ($options as $opt) {
            if (is_string($opt) && is_callable($opt)) {
                $value = $opt($value);
            } elseif (is_array($opt) && isset($opt[0]) && is_callable($opt[0])) {
                $param = array_slice($opt, 1);
                foreach ($param as $k => $v) {
                    $param[$k] = ($v === self::FIELD_VALUE) ? $value : $v;
                }
                $value = call_user_func_array($opt[0], $param);
            } elseif (is_callable($opt)) {
                $value = $opt($value, $field, $this->fieldData, $isObject);
            }
        }
        return $value;
    }
}
