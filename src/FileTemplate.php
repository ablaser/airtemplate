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
 * Class FileTemplate
 *
 * This is an extension of the class Template.
 *
 * Adds methods for loading templates from files and to save and load parsed
 * templates.
 *
 * Example:
 * <code>
 * use AirTemplate\FileTemplate;
 * $engine = new FileTemplate;
 * $engine
 *     ->loadTemplates('*.tmpl', '../templates')
 *     ->saveParsedTemplates('../cache/panel-a.json');
 * $engine->loadParsedTemplates('../cache/panel-a.json');
 * </code>
 */
class FileTemplate extends Template
{

    /**
     * Load templates from external files into an array.
     *
     * Returns an array where the file basenames (including extension) are
     * used as array-keys (e.g. 'table.tmpl').
     *
     * @param string|array $files        Glob() Filemask or array of filenames
     * @param string       $dir          Path to template directory
     * @param bool         $addTemplates Add templates if true, replace otherwise
     *
     * @return object      Returns the object instance
     */
    public function loadTemplates($files, $dir = '', $addTemplates = false)
    {
        $templates = [];
        $tfiles = self::getFilenames($files, $dir);
        foreach ($tfiles as $file) {
            $template = self::readFile($file);
            if ($template === false) {
                throw new \Exception(
                    'File "' . $file . '" is not a file or not readable.',
                    E_USER_WARNING
                );
            }
            $templates[basename($file)] = $template;
        }
        return $this->setTemplates($templates, $addTemplates);
    }

    /**
     * Writes an array of parsed templates to a single file (JSON encoded).
     *
     * @param string $filename Filename
     * @param int    $options  json_encode() options
     *
     * @return mixed False on error, Bytes written on success
     */
    public function saveParsedTemplates($filename, $options = 0)
    {
        return file_put_contents(
            $filename,
            json_encode($this->getParsedTemplates(), $options)
        );
    }

    /**
     * Load JSON encoded templates from file back into a templates array.
     *
     * @param string $filename Filename
     *
     * @return object          The object instance
     */
    public function loadParsedTemplates($filename)
    {
        $res = self::readFile($filename);
        return $this->SetParsedTemplates(
            ($res !== false) ? json_decode($res, true) : []
        );
    }

    /**
     * Build a list of qualified filenames.
     *
     * @param string|array $files        Glob() Filemask or array of filenames
     * @param string       $dir          Path to template directory
     *
     * @return array       Array of filenames
     */
    protected static function getFilenames($files, $dir = '')
    {
        if (is_array($files)) {
            if ($dir == '') {
                return $files;
            }
            $tfiles = [];
            foreach ($files as $f) {
                $tfiles[] = $dir . DIRECTORY_SEPARATOR . $f;
            }
            return $tfiles;
        }
        $tfiles = glob(($dir != '' ? $dir . DIRECTORY_SEPARATOR : '') . $files);
        return ($tfiles !== false) ? $tfiles : [];
    }

    /**
     * Read a file.
     *
     * @param string $filename Filename
     *
     * @return mixed           False on error, file content on success
     */
    protected static function readFile($filename)
    {
        if (is_readable($filename) && is_file($filename)) {
            return file_get_contents($filename);
        }
        return false;
    }
}
