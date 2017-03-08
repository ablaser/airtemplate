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
 * Class BaseEngine
 *
 * The base render engine class.
 * Supports:
 * - Multiple field options (Shortcuts/PHP functions, User functions/methods)
 */
class BaseEngine implements EngineInterface
{

    /**
     * Array of parsed templates.
     *
     * @var array
     */
    protected $templates = [];

    /**
     * Array of fields in templates.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Array of field options specified in templates
     *
     * @var array
     */
    protected $fieldOptions = [];

    /**
     * True if the $data parameter is an object
     *
     * @var bool
     */
    protected $isObject = false;

    /**
     * Constructor.
     *
     * @param array $templates    Array of parsed templates
     * @param array $fieldOptions Array of field options
     */
    public function __construct(array $templates, array $fieldOptions)
    {
        foreach ($templates as $name => $template) {
            $this->templates[$name] = $template['template'];
            $this->fields[$name] = $template['fields'];
        }
        $this->fieldOptions = $fieldOptions;
    }

    /**
     * Renders the template $name using the values in $data. Optionally
     * apply specified field rendering options.
     *
     * @param string       $name    Template name
     * @param array|object $data    Replacement values
     *
     * @return string The rendered output
     */
    public function render($name, $data = [])
    {
        if (!isset($this->templates[$name])) {
            throw new \RuntimeException('Template "' . $name . '" does not exist.');
        }
        return $this->merge($name, $data);
    }

    /**
     * Repeats the template for each item in $data and return the rendered
     * result. Optionally apply specified field rendering options.
     * If a function is given in $rowGenerator, each will send each rendered
     * row (one by one) to the rowGenerator function.
     * There is no return value in this case.
     *
     * @param string              $name         Template name
     * @param array|object|string $data         Data object or array
     * @param string              $separator    Optional separator between items
     * @param \Generator          $rowGenerator A row generator function or null
     *
     * @return string|void The rendered output or nothing in generator mode
     */
    public function each(
        $name,
        $data = [],
        $separator = '',
        \Generator $rowGenerator = null
    ) {
        if (!isset($this->templates[$name])) {
            throw new \RuntimeException(
                'Template "' . $name . '" does not exist.'
            );
        }
        if (is_scalar($data)) {
            // may happen in xml files when a repeatable element
            // occurs only once
            $data = [$data];
        }
        if (isset($rowGenerator)) {
            return $this->eachGenerator($name, $data, $separator, $rowGenerator);
        }
        $rows = 0;
        $buffer = '';
        foreach ($data as $row) {
            $buffer .= $this->renderRow($name, $row, ($rows > 0 ? $separator : ''));
            $rows++;
        }
        return $buffer;
    }

    /**
     * Repeats the template for each item in $data and return the rendered
     * result. Optionally apply specified field rendering options.
     * Send each rendered row (one by one) to the rowGenerator function.
     *
     * @param string       $name         Template name
     * @param array|object $data         Data object or array
     * @param string       $separator    Optional separator between items
     * @param \Generator   $rowGenerator A row generator function
     *
     * @return void
     */
    public function eachGenerator(
        $name,
        $data,
        $separator,
        \Generator $rowGenerator
    ) {
        $rows = 0;
        foreach ($data as $row) {
            $rowGenerator->send(
                $this->renderRow($name, $row, ($rows > 0 ? $separator : ''))
            );
            $rows++;
        }
    }

    /**
     * Render a single row.
     *
     * @param string              $name      Template name
     * @param array|object|string $row       Raw row data
     * @param string              $separator Optional separator between items
     *
     * @return string
     */
    private function renderRow($name, $row, $separator)
    {
        if (is_scalar($row)) {
            $row = ['item' => $row];
        } elseif (is_object($row) && count($row) == 0) {
            // seems the only way to identify objs with only one member
            $row = ['item' => (string) $row[0]];
        }
        return $separator . $this->merge($name, $row);
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
        $this->isObject = is_object($data);
        foreach ($this->templates[$name] as $index => $fragment) {
            if (!isset($this->fields[$name][$index])) {
                $result .= $fragment;
                continue;
            }
            $field = $this->fields[$name][$index];
            $value = $this->getFieldValue($field, $data);
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
     *
     * @return string
     */
    private function getFieldValue($field, $data)
    {
        if ($this->isObject) {
            if (isset($data->$field)) {
                return $data->$field;
            }
            return '';
        }
        if (isset($data[$field])) {
            return $data[$field];
        }
        return '';
    }

    /**
     * Renders the output according to the specified option.
     *
     * @param mixed        $value   Replacement value
     * @param string       $field   Field name
     * @param array|object $data    Data array or object
     * @param mixed        $options Field options
     *
     * @return string Field value
     */
    protected function renderField(
        $value,
        $field,
        $data,
        $options
    ) {
        foreach ($options as $option) {
            switch ($option[0]) {
                case 'default:':
                    if (empty($value)) {
                        return $option[1];
                    }
                    break;
                case 'php:':
                    $value = $this->phpFunction($value, $option[1]);
                    break;
                default:
                    $value = $this->userFunction(
                        $value,
                        $field,
                        $data,
                        $option
                    );
            }
        }
        return $value;
    }

    /**
     * Apply a PHP function to the value.
     *
     * @param mixed        $value  Field value
     * @param array|string $option A single option
     *
     * @return string Field value
     */
    protected function phpFunction($value, $option)
    {
        if (is_string($option)) {
            if (is_callable($option)) {
                return $option($value);
            }
            return $value;
        }
        if (is_callable($option[0])) {
            return $this->phpUserFunction($value, $option);
        }
        return $value;
    }

    /**
     * Apply a PHP function to the value.
     *
     * @param mixed $value  Field value
     * @param array $option A single option
     *
     * @return string Field value
     */
    protected function phpUserFunction($value, $option)
    {
        foreach ($option[1] as $k => $v) {
            $option[1][$k] = (trim($v) === '?') ? $value : $v;
            if (is_numeric($option[1][$k])) {
                if (is_float($option[1][$k])) {
                    $option[1][$k] = floatval($option[1][$k]);
                } elseif (is_int($option[1][$k])) {
                    $option[1][$k] = intval($option[1][$k]);
                }
            }
        }
        return call_user_func_array($option[0], $option[1]);
    }

    /**
     * Apply an option to the field value.
     *
     * @param mixed        $value   Replacement value
     * @param string       $field   Field name
     * @param array|object $data    Data array or object
     * @param mixed        $option  Field option
     *
     * @return string Field value
     */
    protected function userFunction($value, $field, $data, $option)
    {
        switch ($option[0]) {
            case 'data:':
            case 'data::':
                if ($this->isObject) {
                    return $this->dataFunction($value, $data, $option);
                }
                return $value;
            case 'user:':
                $option = $option[1];
        }
        if (is_callable($option)) {
            return $option($value, $field, $data);
        }
        return $value;
    }

    /**
     * Call a data object or static method.
     *
     * @param mixed  $value  Replacement value
     * @param object $data   Data object
     * @param array  $option A single option
     *
     * @return string Field value
     */
    protected function dataFunction($value, $data, array $option)
    {
        $func = false;
        if ($option[0] == 'data::') {
            $func = [get_class($data), $option[1]];
        } elseif ($option[0] == 'data:') {
            $func = [$data, $option[1]];
        }
        if (is_callable($func)) {
            return $func();
        }
        return $value;
    }
}
