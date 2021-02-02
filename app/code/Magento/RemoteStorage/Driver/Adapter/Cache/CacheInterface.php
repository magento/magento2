<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RemoteStorage\Driver\Adapter\Cache;

/**
 * Interface for filesystem adapter cache storage.
 */
interface CacheInterface
{
    /**
     * Check if file data exists in cache.
     *
     * @param string $path
     * @return bool
     */
    public function exists($path);

    /**
     * Read file data from cache.
     *
     * @param string $path
     * @return array|false
     */
    public function getFileContents($path);

    /**
     * Get metadata for path.
     *
     * @param string $path
     * @return array|false
     */
    public function getMetadata($path);

    /**
     * Check is the directory listing complete.
     *
     * @param string $dirname
     * @param bool $recursive
     * @return bool
     */
    public function isDirListingComplete($dirname, $recursive);

    /**
     * Set directory listing complete.
     *
     * @param string $dirname
     * @param bool   $recursive
     */
    public function setDirListingComplete($dirname, $recursive);

    /**
     * Flush the cache.
     */
    public function flushCache();

    /**
     * Save data to storage.
     */
    public function persist();

    /**
     * Load the cache.
     */
    public function load();

    /**
     * Rename/move a file or directory.
     *
     * @param string $path
     * @param string $newpath
     */
    public function moveFile($path, $newpath);

    /**
     * Copy file.
     *
     * @param string $path
     * @param string $newpath
     */
    public function copyFile($path, $newpath);

    /**
     * Delete an object from cache by path.
     *
     * @param string $path
     */
    public function deleteFile($path);

    /**
     * Delete objects in directory for cache.
     *
     * @param string $dirname
     */
    public function deleteDir($dirname);

    /**
     * Update metadata for the path.
     *
     * @param string $path
     * @param array $objectMetadata
     * @param bool $persist
     */
    public function updateMetadata($path, array $objectMetadata, $persist = false);

    /**
     * Reset data in cache for object.
     *
     * @param string $path
     */
    public function resetData($path);
}
