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
 * ParserInterface is the interface all parsers must implement.
 *
 * @author  Andreas Blaser <a.blaser.66@gmail.com>
 */
interface ParserInterface
{
    /**
     * Parses an array of source templates into internal representation.
     *
     * @param array $templates Array of raw templates
     *
     * @return array           An array of parsed templates
     */
    public function parse(array $templates);
}
