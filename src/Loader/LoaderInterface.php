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
 * LoaderInterface is the interface all loaders must implement.
 *
 * @author  Andreas Blaser <a.blaser.66@gmail.com>
 */
interface LoaderInterface
{
    /**
     * Loads a bunch of templates.
     *
     * @param array|string $templates An array of templates,
     *                                filenames or a glob pattern
     *
     * @return array|false An array of templates or false
     */
    public function load($templates);
}
