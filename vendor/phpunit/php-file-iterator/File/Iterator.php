<?php
/**
 * php-file-iterator
 *
 * Copyright (c) 2009-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   File
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2009-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

/**
 * FilterIterator implementation that filters files based on prefix(es) and/or
 * suffix(es). Hidden files and files from hidden directories are also filtered.
 *
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2009-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/php-file-iterator/tree
 * @since     Class available since Release 1.0.0
 */
class File_Iterator extends FilterIterator
{
    const PREFIX = 0;
    const SUFFIX = 1;

    /**
     * @var array
     */
    protected $suffixes = array();

    /**
     * @var array
     */
    protected $prefixes = array();

    /**
     * @var array
     */
    protected $exclude = array();

    /**
     * @var string
     */
    protected $basepath;

    /**
     * @param  Iterator $iterator
     * @param  array    $suffixes
     * @param  array    $prefixes
     * @param  array    $exclude
     * @param  string   $basepath
     */
    public function __construct(Iterator $iterator, array $suffixes = array(), array $prefixes = array(), array $exclude = array(), $basepath = NULL)
    {
        $exclude = array_filter(array_map('realpath', $exclude));

        if ($basepath !== NULL) {
            $basepath = realpath($basepath);
        }

        if ($basepath === FALSE) {
            $basepath = NULL;
        } else {
            foreach ($exclude as &$_exclude) {
                $_exclude = str_replace($basepath, '', $_exclude);
            }
        }

        $this->prefixes = $prefixes;
        $this->suffixes = $suffixes;
        $this->exclude  = $exclude;
        $this->basepath = $basepath;

        parent::__construct($iterator);
    }

    /**
     * @return boolean
     */
    public function accept()
    {
        $current  = $this->getInnerIterator()->current();
        $filename = $current->getFilename();
        $realpath = $current->getRealPath();

        if ($this->basepath !== NULL) {
            $realpath = str_replace($this->basepath, '', $realpath);
        }

        // Filter files in hidden directories.
        if (preg_match('=/\.[^/]*/=', $realpath)) {
            return FALSE;
        }

        return $this->acceptPath($realpath) &&
               $this->acceptPrefix($filename) &&
               $this->acceptSuffix($filename);
    }

    /**
     * @param  string $path
     * @return boolean
     * @since  Method available since Release 1.1.0
     */
    protected function acceptPath($path)
    {
        foreach ($this->exclude as $exclude) {
            if (strpos($path, $exclude) === 0) {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @param  string $filename
     * @return boolean
     * @since  Method available since Release 1.1.0
     */
    protected function acceptPrefix($filename)
    {
        return $this->acceptSubString($filename, $this->prefixes, self::PREFIX);
    }

    /**
     * @param  string $filename
     * @return boolean
     * @since  Method available since Release 1.1.0
     */
    protected function acceptSuffix($filename)
    {
        return $this->acceptSubString($filename, $this->suffixes, self::SUFFIX);
    }

    /**
     * @param  string  $filename
     * @param  array   $subString
     * @param  integer $type
     * @return boolean
     * @since  Method available since Release 1.1.0
     */
    protected function acceptSubString($filename, array $subStrings, $type)
    {
        if (empty($subStrings)) {
            return TRUE;
        }

        $matched = FALSE;

        foreach ($subStrings as $string) {
            if (($type == self::PREFIX && strpos($filename, $string) === 0) ||
                ($type == self::SUFFIX &&
                 substr($filename, -1 * strlen($string)) == $string)) {
                $matched = TRUE;
                break;
            }
        }

        return $matched;
    }
}
