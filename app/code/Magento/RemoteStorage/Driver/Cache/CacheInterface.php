<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Cache;

use League\Flysystem\FilesystemException;
use League\Flysystem\StorageAttributes;
use League\Flysystem\UnableToCheckFileExistence;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;

/**
 * Remote storage cache interface.
 */
interface CacheInterface
{
    public const LIST_SHALLOW = false;

    /**
     * Verify if file exists.
     *
     * @param string $location
     * @return bool
     */
    public function fileExists(string $location): bool;

    /**
     * Read location.
     *
     * @param string $location
     * @return string
     */
    public function read(string $location): string;

    /**
     * Read location.
     *
     * @param string $location
     * @return resource
     */
    public function readStream(string $location);

    /**
     * Retrieve directory list contents.
     *
     * @param string $location
     * @param bool $deep
     * @return iterable<StorageAttributes>
     */
    public function listContents(string $location, bool $deep = self::LIST_SHALLOW): iterable;

    /**
     * Retrieve directory/file last update date.
     * @param string $path
     * @return int
     */
    public function lastModified(string $path): int;

    /**
     * Retrieve file size.
     *
     * @param string $path
     * @return int
     */
    public function fileSize(string $path): int;

    /**
     * Retrieve file mimeType.
     *
     * @param string $path
     * @return string
     */
    public function mimeType(string $path): string;

    /**
     * Get directory/file visibility status.
     *
     * @param string $path
     * @return string
     */
    public function visibility(string $path): string;

    /**
     * Check whether the directory listing of a given directory is complete.
     *
     * @param string $dirname
     * @param bool $recursive
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
}
