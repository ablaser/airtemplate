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

use Psr\Cache\CacheItemPoolInterface;

/**
 * FilesystemLoader reads templates from the filesystem.
 */
class CacheLoader extends FilesystemLoader
{

    /**
     * Template cache.
     *
     * @see Template::__construct()
     *
     * @var object
     */
    protected $cache = null;

    /**
     * Cache TTL in seconds.
     *
     * @see Template::__construct()
     *
     * @var int
     */
    protected $expiresAfter = 3600;

    /**
     * Array of parse options.
     *
     * @see Template::__construct()
     *
     * @var array
     */
    protected $parseOptions = [];

    /**
     * Template directory.
     *
     * @see Template::__construct()
     *
     * @var string
     */
    protected $dir = '';

    /**
     * Constructor.
     *
     * @param CacheItemPoolInterface $cache        Template directory
     * @param int                    $expiresAfter Cache TTL in seconds
     * @param string                 $dir          Template directory
     * @param array                  $parseOptions Template parser options
     */
    public function __construct(
        CacheItemPoolInterface $cache = null,
        $expiresAfter = 3600,
        $dir = '',
        $parseOptions = []
    ) {
        parent::__construct($dir, $parseOptions);
        $this->expiresAfter = $expiresAfter;
        $this->cache = $cache;
    }

    /**
     * Reads template files and returns them as a templates array.
     *
     * @param array|string $templates Array of filenames or file mask (regex).
     *
     * @return array|bool An array of templates or false
     */
    public function load($templates)
    {
        if ($this->cache !== null) {
            $cacheKey = 'airtemplate.' . md5(serialize($templates));
            $cacheItem = $this->cache->getItem($cacheKey);
            $cacheItem->expiresAfter($this->expiresAfter);
            if ($cacheItem->isHit()) {
                if ($this->logger !== null) {
                    $this->logger->debug('Cache hit: ' . $cacheKey);
                }
                return $cacheItem->get();
            }
        }
        $templates = parent::load($templates);
        if ($templates !== false) {
            if ($this->cache !== null) {
                $cacheItem->set($templates);
                $this->cache->save($cacheItem);
                if ($this->logger !== null) {
                    $this->logger->debug('Cache save: ' . $cacheKey);
                }
            }
            return $templates;
        }
        return false;
    }
}
