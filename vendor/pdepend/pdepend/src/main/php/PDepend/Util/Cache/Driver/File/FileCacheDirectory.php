<?php
/**
 * This file is part of PDepend.
 *
 * PHP Version 5
 *
 * Copyright (c) 2008-2015, Manuel Pichler <mapi@pdepend.org>.
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
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.10.0
 */

namespace PDepend\Util\Cache\Driver\File;

use PDepend\Util\Cache\CacheDriver;

/**
 * Directory helper for the file system based cache implementation.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.10.0
 */
class FileCacheDirectory
{
    /**
     * The current cache version/hash number.
     */
    const VERSION = CacheDriver::VERSION;

    /**
     * The cache root directory.
     *
     * @var string
     */
    protected $cacheDir = null;

    /**
     * Constructs a new cache directory helper instance.
     *
     * @param string $cacheDir The cache root directory.
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $this->ensureExists($cacheDir);

        if (false === $this->isValidVersion()) {
            $this->flush();
        }
    }

    /**
     * Creates a cache directory for the given cache entry key and returns the
     * full qualified path for that cache directory.
     *
     * @param  string $key The cache for an entry.
     * @return string
     */
    public function createCacheDirectory($key)
    {
        return $this->createOrReturnCacheDirectory($key);
    }

    /**
     * Returns the full qualified path for an existing cache directory or
     * creates a new cache directory for the given cache entry key and returns
     * the full qualified path for that cache directory.
     *
     * @param  string $key The cache for an entry.
     * @return string
     */
    protected function createOrReturnCacheDirectory($key)
    {
        $path = $this->getCacheDir() . '/' . substr($key, 0, 2);
        if (false === file_exists($path)) {
            @mkdir($path, 0775, true);
        }
        return $path;
    }

    /**
     * Ensures that the given <b>$cacheDir</b> really exists.
     *
     * @param  string $cacheDir The cache root directory.
     * @return string
     */
    protected function ensureExists($cacheDir)
    {
        if (false === file_exists($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        return $cacheDir;
    }

    /**
     * Tests if the current software cache version is similar to the stored
     * file system cache version.
     *
     * @return boolean
     */
    protected function isValidVersion()
    {
        return (self::VERSION === $this->readVersion());
    }

    /**
     * Reads the stored cache version number from the cache root directory.
     *
     * @return string
     */
    protected function readVersion()
    {
        if (file_exists($this->getVersionFile())) {
            return trim(file_get_contents($this->getVersionFile()));
        }
        return null;
    }

    /**
     * Writes the current software cache version into a file in the cache root
     * directory.
     *
     * @return void
     */
    protected function writeVersion()
    {
        file_put_contents($this->getVersionFile(), self::VERSION, LOCK_EX);
    }

    /**
     * Returns the file name for the used version file.
     *
     * @return string
     */
    protected function getVersionFile()
    {
        return $this->getCacheDir() . '/_version';
    }

    /**
     * Returns the cache root directory.
     *
     * @return string
     */
    protected function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Flushes all contents below the configured cache root directory and writes
     * a version file with the current software version.
     *
     * @return void
     */
    protected function flush()
    {
        $this->flushDirectory($this->getCacheDir());
        $this->writeVersion();
    }

    /**
     * Deletes all files and directories below the given <b>$cacheDir</b>.
     *
     * @param string $cacheDir A cache directory.
     *
     * @return void
     */
    protected function flushDirectory($cacheDir)
    {
        foreach (new \DirectoryIterator($cacheDir) as $child) {
            $this->flushEntry($child);
        }
    }

    /**
     * Flushes the cache record for the given file info instance, independent if
     * it is a file, directory or symlink.
     *
     * @param  \SplFileInfo $file
     * @return void
     */
    protected function flushEntry(\SplFileInfo $file)
    {
        $path = $file->getRealPath();
        if ($file->isDot()) {
            return;
        } elseif ($file->isFile()) {
            @unlink($path);
        } else {
            $this->flushDirectory($path);
            @rmdir($path);
        }
    }
}
