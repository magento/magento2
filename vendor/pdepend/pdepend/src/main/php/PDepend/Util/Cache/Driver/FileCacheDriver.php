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

namespace PDepend\Util\Cache\Driver;

use PDepend\Util\Cache\CacheDriver;
use PDepend\Util\Cache\Driver\File\FileCacheDirectory;
use PDepend\Util\Cache\Driver\File\FileCacheGarbageCollector;

/**
 * A file system based cache implementation.
 *
 * This class implements the {@link \PDepend\Util\Cache\CacheDriver} interface
 * based on the local file system. It creates a special directory structure and
 * stores all cache entries in files under this directory structure.
 *
 * @copyright 2008-2015 Manuel Pichler. All rights reserved.
 * @license http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since 0.10.0
 */
class FileCacheDriver implements CacheDriver
{
    /**
     * Default cache entry type.
     */
    const ENTRY_TYPE = 'cache';

    /**
     * The cache directory handler
     *
     * @var FileCacheDirectory
     */
    protected $directory;

    /**
     * The current cache entry type.
     *
     * @var string
     */
    protected $type = self::ENTRY_TYPE;

    /**
     * Major and minor version of the currently used PHP.
     *
     * @var string
     */
    protected $version;

    /**
     * Unique key for this cache instance.
     *
     * @var   string
     * @since 1.0.0
     */
    private $cacheKey;

    /**
     * @var \PDepend\Util\Cache\Driver\File\FileCacheGarbageCollector
     */
    private $garbageCollector;

    /**
     * This method constructs a new file cache instance for the given root
     * directory.
     *
     * @param string $root     The cache root directory.
     * @param string $cacheKey Unique key for this cache instance.
     */
    public function __construct($root, $cacheKey = null)
    {
        $this->directory = new FileCacheDirectory($root);
        $this->version   = preg_replace('(^(\d+\.\d+).*)', '\\1', phpversion());

        $this->cacheKey = $cacheKey;

        $this->garbageCollect($root);
    }

    /**
     * Sets the type for the next <em>store()</em> or <em>restore()</em> method
     * call. A type is something like a namespace or group for cache entries.
     *
     * Note that the cache type will be reset after each storage method call, so
     * you must invoke right before every call to <em>restore()</em> or
     * <em>store()</em>.
     *
     * @param  string $type The name or object type for the next storage method call.
     * @return \PDepend\Util\Cache\CacheDriver
     */
    public function type($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * This method will store the given <em>$data</em> under <em>$key</em>. This
     * method can be called with a third parameter that will be used as a
     * verification token, when the a cache entry gets restored. If the stored
     * hash and the supplied hash are not identical, that cache entry will be
     * removed and not returned.
     *
     * @param  string $key  The cache key for the given data.
     * @param  mixed  $data Any data that should be cached.
     * @param  string $hash Optional hash that will be used for verification.
     * @return void
     */
    public function store($key, $data, $hash = null)
    {
        $file = $this->getCacheFile($key);
        $this->write($file, serialize(array('hash' => $hash, 'data' => $data)));
    }

    /**
     * This method writes the given <em>$data</em> into <em>$file</em>.
     *
     * @param  string $file The cache file name.
     * @param  string $data Serialized cache data.
     * @return void
     */
    protected function write($file, $data)
    {
        $handle = fopen($file, 'wb');
        flock($handle, LOCK_EX);
        fwrite($handle, $data);
        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * This method tries to restore an existing cache entry for the given
     * <em>$key</em>. If a matching entry exists, this method verifies that the
     * given <em>$hash</em> and the the value stored with cache entry are equal.
     * Then it returns the cached entry. Otherwise this method will return
     * <b>NULL</b>.
     *
     * @param  string $key  The cache key for the given data.
     * @param  string $hash Optional hash that will be used for verification.
     * @return mixed
     */
    public function restore($key, $hash = null)
    {
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            return $this->restoreFile($file, $hash);
        }
        return null;
    }

    /**
     * This method restores a cache entry, when the given <em>$hash</em> is equal
     * to stored hash value. If both hashes are equal this method returns the
     * cached entry. Otherwise this method returns <b>NULL</b>.
     *
     * @param  string $file The cache file name.
     * @param  string $hash The verification hash.
     * @return mixed
     */
    protected function restoreFile($file, $hash)
    {
        $data = unserialize($this->read($file));
        if ($data['hash'] === $hash) {
            return $data['data'];
        }
        return null;
    }

    /**
     * This method reads the raw data from the given <em>$file</em>.
     *
     * @param  string $file The cache file name.
     * @return string
     */
    protected function read($file)
    {
        $handle = fopen($file, 'rb');
        flock($handle, LOCK_EX);

        $data = fread($handle, filesize($file));

        flock($handle, LOCK_UN);
        fclose($handle);

        return $data;
    }

    /**
     * This method will remove an existing cache entry for the given identifier.
     * It will delete all cache entries where the cache key start with the given
     * <b>$pattern</b>. If no matching entry exists, this method simply does
     * nothing.
     *
     * @param  string $pattern The cache key pattern.
     * @return void
     */
    public function remove($pattern)
    {
        $file = $this->getCacheFileWithoutExtension($pattern);
        $glob = glob("{$file}*.*");
        // avoid error if we dont find files
        if ($glob !== false) {
            foreach (glob("{$file}*.*") as $f) {
                unlink($f);
            }
        }
    }

    /**
     * This method creates the full qualified file name for a cache entry. This
     * file name is a combination of the given <em>$key</em>, the cache root
     * directory and the current entry type.
     *
     * @param  string $key The cache key for the given data.
     * @return string
     */
    protected function getCacheFile($key)
    {
        $cacheFile = $this->getCacheFileWithoutExtension($key) .
                     '.' . $this->version .
                     '.' . $this->type;

        $this->type = self::ENTRY_TYPE;

        return $cacheFile;
    }

    /**
     * This method creates the full qualified file name for a cache entry. This
     * file name is a combination of the given <em>$key</em>, the cache root
     * directory and the current entry type, but without the used cache file
     * extension.
     *
     * @param  string $key The cache key for the given data.
     * @return string
     */
    protected function getCacheFileWithoutExtension($key)
    {
        if (is_string($this->cacheKey)) {
            $key = md5($key . $this->cacheKey);
        }

        $path = $this->directory->createCacheDirectory($key);
        return "{$path}/{$key}";
    }

    /**
     * Cleans old cache files.
     *
     * @param string $root
     * @return void
     */
    protected function garbageCollect($root)
    {
        $garbageCollector = new FileCacheGarbageCollector($root);
        $garbageCollector->garbageCollect();
    }
}
