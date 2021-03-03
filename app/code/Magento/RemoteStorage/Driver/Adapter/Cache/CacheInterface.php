<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter\Cache;

/**
 * Interface for filesystem adapter cache storage. Used in Cached adapter implementation to store metadata for
 * filesystem entities in order to reduce calls to filesystem API.
 */
interface CacheInterface
{
    /**
     * Check if file data exists in cache.
     *
     * @param string $path
     * @return bool
     */
    public function exists(string $path): bool;

    /**
     * Read file data from cache.
     *
     * @param string $path
     * @return array|null
     */
    public function getFileContents(string $path): ?array;

    /**
     * Get metadata for path.
     *
     * @param string $path
     * @return array|null
     */
    public function getMetadata(string $path): ?array;

    /**
     * Flush the cache.
     */
    public function flushCache(): void;

    /**
     * Save data to storage.
     */
    public function persist(): void;

    /**
     * Load the cache.
     */
    public function load(): void;

    /**
     * Rename/move a file or directory.
     *
     * @param string $path
     * @param string $newpath
     */
    public function moveFile(string $path, string $newpath): void;

    /**
     * Copy file.
     *
     * @param string $path
     * @param string $newpath
     */
    public function copyFile(string $path, string $newpath): void;

    /**
     * Delete an object from cache by path.
     *
     * @param string $path
     */
    public function deleteFile(string $path): void;

    /**
     * Delete objects in directory for cache.
     *
     * @param string $dirname
     */
    public function deleteDir(string $dirname): void;

    /**
     * Update metadata for the path.
     *
     * @param string $path
     * @param array $objectMetadata
     * @param bool $persist
     */
    public function updateMetadata(string $path, array $objectMetadata, bool $persist = false): void;

    /**
     * Reset data in cache for object.
     *
     * @param string $path
     */
    public function resetData(string $path): void;
}
