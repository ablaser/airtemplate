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
 * FilesystemLoader reads templates from the filesystem.
 */
class FilesystemLoader extends Loader
{
    /**
     * Template directory.
     *
     * @var string
     */
    protected $dir = '';

    /**
     * Constructor.
     *
     * @param string $dir          Path to template directory
     * @param array  $parseOptions Template parser options
     */
    public function __construct($dir = '', $parseOptions = [])
    {
        $this->dir = $dir;
        $this->parseOptions = $parseOptions;
    }

    /**
     * Reads template files and returns them as a templates array.
     *
     * @param string|array $templates Glob pattern or array of filenames
     *
     * @return array|false An array of templates or false
     */
    public function load($templates)
    {
        $templates = $this->loadTemplateFiles($templates);
        if (!empty($templates)) {
            return $this->parseTemplates($templates);
        }
        return false;
    }

    /**
     * Sets a new template directory.
     *
     * @param string $dir Path to template directory
     *
     * @return FilesystemLoader
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
        return $this;
    }

    /**
     * Get the current template directory.
     *
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * Load templates from external files into an array.
     *
     * Returns an array where the file basenames (including extension) are
     * used as array-keys (e.g. 'table.tmpl').
     *
     * @param string|array $files Glob pattern or array of filenames
     *
     * @return array Array of source templates
     */
    protected function loadTemplateFiles($files)
    {
        $templates = [];
        $failures = [];
        $files = $this->getFilenames($files);
        foreach ($files as $name => $file) {
            $temp = self::readFile($file);
            if ($temp === false) {
                $failures[] = $file;
                continue;
            }
            $templates[$name] = $temp;
            $this->debugLog('Template loaded: ' . $file);
        }
        if (!empty($templates)) {
            $this->debugLog('Templates loaded: ' . count($templates));
        } else {
            foreach ($failures as $file) {
                $this->debugLog('Template not loaded: ' . $file);
            }
        }
        return $templates;
    }


    /**
     * Write debug log message if a logger is set.
     *
     * @param string $msg The log message
     *
     * @return void
     */
    private function debugLog($msg)
    {
        if ($this->logger !== null) {
            $this->logger->debug($msg);
        }
    }

    /**
     * Build a list of qualified filenames.
     *
     * The keys of the returned array represents the template names.
     * Non-numeric keys in the incoming array will be used as template
     * names, otherwise the file basename is used.
     *
     * @param string|array $files Glob pattern or array of filenames
     *
     * @return array Array of filenames
     */
    protected function getFilenames($files)
    {
        if (!is_array($files)) {
            return $this->matchFilenames($files);
        }
        $fnames = [];
        foreach ($files as $key => $file) {
            if (is_numeric($key)) {
                $key = explode('.', basename($file))[0];
            }
            $fnames[$key] = ($this->dir != '')
                ? $this->dir . DIRECTORY_SEPARATOR . $file
                : $file;
        }
        return $fnames;
    }

    /**
     * Get a list of files from $dir matching $pattern.
     *
     * @param string $pattern Regular expression
     *
     * @return array Array of filenames
     */
    protected function matchFilenames($pattern)
    {
        $files = new \GlobIterator(realpath($this->dir) . DIRECTORY_SEPARATOR . $pattern);
        $filenames = [];
        foreach ($files as $fileinfo) {
            $name = explode('.', $fileinfo->getBasename())[0];
            $filenames[$name] = $fileinfo->getPathname();
        }
        return $filenames;
    }

    /**
     * Read a file.
     *
     * @param string $filename Filename
     *
     * @return string|false File content or false on error
     */
    protected static function readFile($filename)
    {
        if (is_readable($filename) && is_file($filename)) {
            return file_get_contents($filename);
        }
        return false;
    }
}
