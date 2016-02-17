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
 * @since     File available since Release 1.3.0
 */

/**
 * Façade implementation that uses File_Iterator_Factory to create a
 * File_Iterator that operates on an AppendIterator that contains an
 * RecursiveDirectoryIterator for each given path. The list of unique
 * files is returned as an array.
 *
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2009-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @version   Release: @package_version@
 * @link      http://github.com/sebastianbergmann/php-file-iterator/tree
 * @since     Class available since Release 1.3.0
 */
class File_Iterator_Facade
{
    /**
     * @param  array|string $paths
     * @param  array|string $suffixes
     * @param  array|string $prefixes
     * @param  array        $exclude
     * @param  boolean      $commonPath
     * @return array
     */
    public function getFilesAsArray($paths, $suffixes = '', $prefixes = '', array $exclude = array(), $commonPath = FALSE)
    {
        if (is_string($paths)) {
            $paths = array($paths);
        }

        $factory  = new File_Iterator_Factory;
        $iterator = $factory->getFileIterator(
          $paths, $suffixes, $prefixes, $exclude
        );

        $files = array();

        foreach ($iterator as $file) {
            $file = $file->getRealPath();

            if ($file) {
                $files[] = $file;
            }
        }

        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = realpath($path);
            }
        }

        $files = array_unique($files);
        sort($files);

        if ($commonPath) {
            return array(
              'commonPath' => $this->getCommonPath($files),
              'files'      => $files
            );
        } else {
            return $files;
        }
    }

    /**
     * Returns the common path of a set of files.
     *
     * @param  array $files
     * @return string
     */
    protected function getCommonPath(array $files)
    {
        $count = count($files);

        if ($count == 0) {
            return '';
        }

        if ($count == 1) {
            return dirname($files[0]) . DIRECTORY_SEPARATOR;
        }

        $_files = array();

        foreach ($files as $file) {
            $_files[] = $_fileParts = explode(DIRECTORY_SEPARATOR, $file);

            if (empty($_fileParts[0])) {
                $_fileParts[0] = DIRECTORY_SEPARATOR;
            }
        }

        $common = '';
        $done   = FALSE;
        $j      = 0;
        $count--;

        while (!$done) {
            for ($i = 0; $i < $count; $i++) {
                if ($_files[$i][$j] != $_files[$i+1][$j]) {
                    $done = TRUE;
                    break;
                }
            }

            if (!$done) {
                $common .= $_files[0][$j];

                if ($j > 0) {
                    $common .= DIRECTORY_SEPARATOR;
                }
            }

            $j++;
        }

        return DIRECTORY_SEPARATOR . $common;
    }
}
