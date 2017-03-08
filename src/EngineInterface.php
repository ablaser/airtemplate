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
 * EngineInterface is the interface all render engines must implement.
 *
 * @author  Andreas Blaser <a.blaser.66@gmail.com>
 */
interface EngineInterface
{

    /**
     * Renders the template $name using the values in $data. Optionally
     * apply specified field rendering options.
     *
     * @param string       $name    Template name
     * @param array|object $data    Replacement values
     *
     * @return string      The rendered output
     */
    public function render($name, $data = []);

    /**
     * Repeats the template for each item in $data and return the rendered
     * result. Optionally apply specified field rendering options.
     * If a function is given in $rowGenerator, each will send each rendered
     * row (one by one) to the rowGenerator function.
     * There is no return value in this case.
     *
     * @param string     $name         Template name
     * @param array      $data         Simple array or 2-dim assoc. array
     * @param string     $separator    Optional separator between items
     * @param \Generator $rowGenerator A row generator function or null
     *
     * @return string|void             The rendered output or nothing in generator mode
     */
    public function each(
        $name,
        $data,
        $separator = '',
        \Generator $rowGenerator = null
    );
}
