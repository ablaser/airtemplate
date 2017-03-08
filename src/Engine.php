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
 * Class Engine
 *
 * The extended render engine class.
 * Supports:
 * - Everything from the BaseEngine class
 * - Nested templates: {{field|render("sub-template")}}, {{field|each("sub-template")}}
 * - Datapath: {{field=rel-path/to/value}}, {{field=/abs-path/to/value}}
 */
class Engine extends BaseEngine
{

    /**
     * Complexity level (0=low, 1=high)
     *
     * @var int
     */
    protected $complexity = [];

    /**
     * Array of 'path' arrays pointing to other locations in data.
     *
     * @var array
     */
    protected $datapath = [];

    /**
     * Translation table for each separator.
     *
     * @var array
     */
    protected $escapeChars = [
        '\n' => "\n",
        '\r' => "\r",
        '\t' => "\t",
        '\v' => "\v",
        '\e' => "\e",
        '\f' => "\f",
        '\\\\' => "\\",
    ];

    /**
     * Constructor.
     *
     * @param array $templates    Array of parsed templates
     * @param array $fieldOptions Array of field options
     */
    public function __construct(array $templates, array $fieldOptions)
    {
        parent::__construct($templates, $fieldOptions);
        foreach ($templates as $name => $template) {
            $this->datapath[$name] = $template['datapath'];
            $this->complexity[$name] = $template['complexity'];
        }
    }

    /**
     * Merge template and values and return the rendered string. Replacement
     * values may be specified as an assoc array or an object.
     *
     * @param string       $name     Template name
     * @param array|object $data     Data object or array
     *
     * @return string The rendered result
     */
    protected function merge($name, $data)
    {
        $result = '';
        if ($this->complexity[$name] < 1) {
            return parent::merge($name, $data);
        }
        $this->isObject = is_object($data);
        foreach ($this->templates[$name] as $index => $fragment) {
            if (!isset($this->fields[$name][$index])) {
                $result .= $fragment;
                continue;
            }
            $field = $this->fields[$name][$index];
            $value = $this->getValue(
                $field,
                $data,
                $this->datapath[$name][$field]
            );
            if ($this->fieldOptions[$name][$field] !== false) {
                $value = $this->renderField(
                    $value,
                    $field,
                    $data,
                    $this->fieldOptions[$name][$field]
                );
            }
            $result .= $value;
        }
        return $result;
    }

    /**
     * get the value for a field.
     *
     * @param string       $field    Field name
     * @param array|object $data     Data array or object
     * @param array|bool   $datapath A datapath array or false
     *
     * @return string
     */
    private function getValue($field, $data, $datapath)
    {
        return $this->isObject
            ? self::getObjectValue($field, $data, $datapath)
            : self::getArrayValue($field, $data, $datapath);
    }

    /**
     * get the value for a field or an empty string.
     *
     * @param string     $field    Field name
     * @param array      $data     Data array
     * @param array|bool $datapath A datapath array or false
     *
     * @return string
     */
    private static function getArrayValue($field, $data, $datapath)
    {
        if ($datapath === false && isset($data[$field])) {
            return $data[$field];
        }
        if ($datapath !== false) {
            return self::queryArray($datapath, $data);
        }
        return '';
    }

    /**
     * Lookup a value in the data array.
     *
     * @param array $keys An array of access keys
     * @param array $data Data array
     *
     * @return mixed Field value or empty string
     */
    private static function queryArray(array $keys, array $data)
    {
        $result = $data;
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                return '';
            }
            $result = &$result[$key];
        }
        return $result;
    }

    /**
     * get the value for a field or an empty string.
     *
     * @param string     $field    Field name
     * @param object     $data     Data object
     * @param array|bool $datapath A datapath array or false
     *
     * @return string
     */
    private static function getObjectValue($field, $data, $datapath)
    {
        if ($datapath === false && isset($data->$field)) {
            return $data->$field;
        }
        if ($datapath !== false) {
            return self::queryObject($datapath, $data);
        }
        return '';
    }

    /**
     * Lookup a value in the data object.
     *
     * @param array  $keys An array of access keys
     * @param object $data Data object
     *
     * @return string
     */
    private static function queryObject(array $keys, $data)
    {
        $result = $data;
        foreach ($keys as $key) {
            if ($key[0] == '@') {
                return self::queryObjectAttr(substr($key, 1), $result);
            }
            $res = (array) $result;
            if (isset($res[$key])) {
                $result = &$res[$key];
                continue;
            }
            if (isset($result->{$key})) {
                $result = $result->{$key};
                continue;
            }
            return '';
        }
        return $result;
    }

    /**
     * Lookup an attribute value in the data element.
     *
     * @param string $name    Attribute name
     * @param object $element Object property
     *
     * @return string Attribute value or empty string
     */
    private static function queryObjectAttr($name, $element)
    {
        if (is_a($element, 'SimpleXMLElement')) {
            $attr = $element->attributes();
            if (isset($attr[$name])) {
                return (string) $attr[$name];
            }
        }
        return '';
    }

    /**
     * Renders the output according to the specified options.
     *
     * @param mixed        $value    Replacement value
     * @param string       $field    Field name
     * @param array|object $data     Data array or object
     * @param mixed        $options  Fields options
     *
     * @return string The formatted value
     */
    protected function renderField(
        $value,
        $field,
        $data,
        $options
    ) {
        foreach ($options as $option) {
            if ($option[0] == 'default:' && empty($value)) {
                return $option[1];
            }
            $value = $this->processOption(
                $value,
                $field,
                $data,
                $option
            );
        }
        return $value;
    }

    /**
     * Apply a single option to the field value.
     *
     * @param mixed        $value    Replacement value
     * @param string       $field    Field name
     * @param array|object $data     Data array or object
     * @param mixed        $option   A single option
     *
     * @return string
     */
    private function processOption(
        $value,
        $field,
        $data,
        $option
    ) {
        switch ($option[0]) {
            case 'php:':
                return $this->phpFunction($value, $option[1]);
            case 'self:':
                return $this->renderSubTemplate($value, $data, $option[1]);
        }
        return $this->userFunction($value, $field, $data, $option);
    }

    /**
     * Renders a sub-template.
     *
     * When the ? parameter is NOT set, we go a level deeper,
     * otherwise we stay on the same level and we pass $data
     * instead of $value to render or each.
     *
     * @param mixed        $value  Field value
     * @param array|object $data   Data array or object
     * @param array        $option A single option
     *
     * @return string Field value
     */
    protected function renderSubTemplate($value, $data, $option)
    {
        if ($option[0] == 'each') {
            $sep = '';
            if (isset($option[1][1])) {
                $sep = strtr($option[1][1], $this->escapeChars);
            }
            return $this->each($option[1][0], $value, $sep);
        }
        if (isset($option[1][1]) && trim($option[1][1]) == '?') {
            $value = $data;
        }
        return $this->render($option[1][0], $value);
    }
}
