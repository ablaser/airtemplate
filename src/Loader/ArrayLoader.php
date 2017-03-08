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

namespace AirTemplate\Loader;

/**
 * ArrayLoader loads templates from memory.
 */
class ArrayLoader extends Loader
{
    /**
     * Constructor.
     *
     * @param array $parseOptions Template parser options
     */
    public function __construct($parseOptions = [])
    {
        $this->parseOptions = $parseOptions;
    }

    /**
     * Returns the incoming templates if it is an array.
     *
     * @param array $templates An array of templates,
     *
     * @return array|bool An array of templates or false
     */
    public function load($templates)
    {
        if (is_array($templates) && !empty($templates)) {
            return $this->parseTemplates($templates);
        }
        return false;
    }
}
