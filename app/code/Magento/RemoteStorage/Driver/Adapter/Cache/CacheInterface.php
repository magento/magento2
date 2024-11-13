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
     * Cache tag
     */
    public const CACHE_TAG = 'flysystem';

    /**
     * Check if path is cached as existing path.
     * Returns:
     *  - true - path exists and there is a cache record for it
     *  - false - path is cached as non-existing in filesystem
     *  - null - no cached record on path
     *
     * @param string $path
     * @return bool|null
     */
    public function exists(string $path): ?bool;

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
     * Purges data enqueued for deletion.
     */
    public function purgeQueue(): void;

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
     * Store flag that path does not exist in the filesystem.
     *
     * @param string $path
     */
    public function storeFileNotExists(string $path): void;
}
