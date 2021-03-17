<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Cache;

use League\Flysystem\FilesystemReader;

/**
 * Remote storage cache interface.
 */
interface CacheInterface extends FilesystemReader
{
    /**
     * Check whether the directory listing of a given directory is complete.
     *
     * @param string $dirname
     * @param bool $recursive
     *
     * @return bool
     */
    public function isComplete(string $dirname, bool $recursive): bool;

    /**
     * Set a directory to completely listed.
     *
     * @param string $dirname
     * @param bool $recursive
     * @return void
     */
    public function setComplete(string $dirname, bool $recursive): void;

    /**
     * Store the contents of a directory.
     *
     * @param string $directory
     * @param array $contents
     * @param bool $recursive
     * @return void
     */
    public function storeContents(string $directory, array $contents, bool $recursive): void;

    /**
     * Flush the cache.
     *
     * @return void
     */
    public function flush(): void;

    /**
     * Autosave trigger.
     *
     * @return void
     */
    public function autosave(): void;

    /**
     * Store the cache.
     *
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function save(): void;

    /**
     * Load the cache.
     *
     * @return void
     * @throws \League\Flysystem\FilesystemException
     */
    public function load(): void;

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newPath
     * @return void
     */
    public function rename(string $path, string $newPath): void;

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     * @return void
     */
    public function copy(string $path, string $newpath): void;

    /**
     * Delete an object from cache.
     *
     * @param string $path object path
     * @return void
     */
    public function delete(string $path): void;

    /**
     * Delete all objects from from a directory.
     *
     * @param string $dirname directory path
     * @return void
     */
    public function deleteDir(string $dirname): void;

    /**
     * Update the metadata for an object.
     *
     * @param string $path
     * @param array $object
     * @param bool $autoSave
     */
    public function updateObject(string $path, array $object, bool $autoSave = false): void;

    /**
     * Store object hit miss.
     *
     * @param string $path
     * @return void
     */
    public function storeMiss(string $path): void;
}
