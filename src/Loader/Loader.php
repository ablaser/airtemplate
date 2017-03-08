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

use AirTemplate\Parser;
use Psr\Log\LoggerInterface;

/**
 * Class Loader
 *
 * The template loader class.
 */
abstract class Loader implements LoaderInterface
{
    /**
     * Array of parse options.
     *
     * @var array
     */
    protected $parseOptions = [];

    /**
     * PSR-3 compatible logger
     *
     * @var LoggerInterface
     */
    protected $logger = null;

    /**
     * Sets the debug logger to use for this loader.
     *
     * @param LoggerInterface $logger A logger instance
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns parsed templates.
     *
     * @param array $templates An array of templates
     *
     * @return array
     */
    protected function parseTemplates($templates)
    {
        $parser = new Parser($this->parseOptions);
        return $parser->parse($templates);
    }
}
