#!/usr/bin/env php
<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2013, Manuel Pichler <mapi@pdepend.org>.
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
 *   * Neither the name of Manuel Pichler nor the names of his
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
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */

namespace PDepend;

/**
 * Utility class that we use to recalculate the cache hash/version.
 *
 * @copyright 2008-2013 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class CacheVersionUpdater
{
    /**
     * The source directory.
     *
     * @var string
     */
    private $rootDirectory = null;

    /**
     * The source sub directories that we will process.
     *
     * @var array(string)
     */
    private $localPaths = array(
        '/Source',
        '/Metrics',
    );

    /**
     * The target file, where this script will persist the new cache version.
     *
     * @var string
     */
    private $targetFile = '/Util/Cache/CacheDriver.php';

    /**
     * Regular expression used to replace a previous cache version.
     *
     * @var string
     */
    private $targetRegexp = '(@version:[a-f0-9]{32}:@)';

    /**
     * Constructs a new cache version updater instance.
     */
    public function __construct()
    {
        $this->rootDirectory = realpath(dirname(__FILE__) . '/../src/main/php/PDepend');
    }

    /**
     * Processes all source files and generates a combined version for all files.
     * The it replaces the old version key within the project source with the
     * newly calculated value.
     *
     * @return void
     */
    public function run()
    {
        $checksum = '';

        foreach ($this->localPaths as $localPath) {
            $path = $this->rootDirectory . $localPath;
            foreach ($this->readFiles($path) as $file) {
                $checksum = $this->hash($file, $checksum);
            }
        }

        $file = $this->rootDirectory . $this->targetFile;

        $code = file_get_contents($file);
        $code = preg_replace($this->targetRegexp, "@version:{$checksum}:@", $code);
        file_put_contents($file, $code);
    }

    /**
     * Generates a hash value for the given <b>$path</b> in combination with a
     * previous calculated <b>$checksum</b>.
     *
     * @param string $path Path to the current context file.
     * @param string $checksum Hash/Checksum for all previously parsed files.
     * @return string
     */
    protected function hash($path, $checksum)
    {
        return md5($checksum . md5_file($path));
    }

    /**
     * Reads all files below the given <b>$path</b>.
     *
     * @param string $path The parent directory or a file.
     * @return array(string)
     */
    protected function readFiles($path)
    {
        if ($this->accept($path)) {
            return array($path);
        }
        $files = array();
        foreach ($this->createFileIterator($path) as $file) {
            if ($this->accept($file)) {
                $files[] = (string) $file;
            }
        }
        return $files;
    }

    /**
     * Does the given path represent a file that has the expected file extension?
     *
     * @param string $path Path to a file or directory.
     * @return boolean
     */
    protected function accept($path)
    {
        return (is_file($path) && '.php' === substr($path, -4, 4));
    }

    /**
     * Creates an iterator with all files below the given directory.
     *
     * @param string $path Path to a directory.
     * @return \Iterator
     */
    protected function createFileIterator($path)
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path)
        );
    }

    /**
     * The main method starts the cache version updater.
     *
     * @param array $args Cli arguments.
     * @return void
     */
    public static function main(array $args)
    {
        $updater = new CacheVersionUpdater();
        $updater->run();
    }
}

CacheVersionUpdater::main($_SERVER['argv']);
