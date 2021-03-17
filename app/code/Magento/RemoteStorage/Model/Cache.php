<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

use League\Flysystem\DirectoryAttributes;
use League\Flysystem\DirectoryListing;
use League\Flysystem\FileAttributes;
use League\MimeTypeDetection\MimeTypeDetector;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\RemoteStorage\Driver\Cache\CacheInterface;
use Magento\RemoteStorage\Model\Storage\CacheStorage;
use Magento\RemoteStorage\Model\Storage\Handler\CacheStorageHandlerInterface;

/**
 * Remote storage cache model.
 */
class Cache implements CacheInterface
{
    /**
     * @var bool
     */
    private bool $autoSave = true;

    /**
     * @var CacheStorageHandlerInterface
     */
    private CacheStorageHandlerInterface $cacheStorageHandler;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var GetPathInfo
     */
    private GetPathInfo $getPathInfo;

    /**
     * @var MimeTypeDetector
     */
    private MimeTypeDetector $mimeTypeDetector;

    /**
     * @var CacheStorage
     */
    private CacheStorage $cacheStorage;

    /**
     * @param CacheStorageHandlerInterface $cacheStorageHandler
     * @param Json $json
     * @param GetPathInfo $getPathInfo
     * @param MimeTypeDetector $mimeTypeDetector
     * @param CacheStorage $cacheStorage
     */
    public function __construct(
        CacheStorageHandlerInterface $cacheStorageHandler,
        Json $json,
        GetPathInfo $getPathInfo,
        MimeTypeDetector $mimeTypeDetector,
        CacheStorage $cacheStorage
    ) {
        $this->json = $json;
        $this->mimeTypeDetector = $mimeTypeDetector;
        $this->getPathInfo = $getPathInfo;
        $this->cacheStorageHandler = $cacheStorageHandler;
        $this->cacheStorage = $cacheStorage;
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if (!$this->autoSave) {
            $this->save();
        }
    }

    /**
     * @inheirtdoc
     */
    public function save(): void
    {
        $this->cacheStorageHandler->save();
    }

    /**
     * @inheirtdoc
     */
    public function load(): void
    {
        $this->cacheStorageHandler->load();
    }

    /**
     * Store the contents listing.
     *
     * @param string $directory
     * @param array $contents
     * @param bool $recursive
     */
    public function storeContents(string $directory, array $contents, $recursive = false): void
    {
        $directories = [$directory];
        foreach ($contents as $object) {
            $object = $object->jsonSerialize();
            $this->updateObject($object['path'], $object);
            $object = $this->cacheStorage->getCacheDataByKey($object['path']);

            if ($recursive && $this->pathIsInDirectory($directory, $object['path'])) {
                $directories[] = $object['dirname'];
            }
        }
        foreach (array_unique($directories) as $directory) {
            $this->setComplete($directory, $recursive);
        }

        $this->autosave();
    }

    /**
     * @inheirtdoc
     */
    public function updateObject(string $path, array $object, $autoSave = false): void
    {
        if (!$this->fileExists($path)) {
            $data = $this->getPathInfo->execute($path);
            $this->cacheStorage->setCacheDataByKey($path, $data);
        }
        $data = array_merge($this->cacheStorage->getCacheDataByKey($path), $object);
        $this->cacheStorage->setCacheDataByKey($path, $data);

        if ($autoSave) {
            $this->autosave();
        }

        $this->ensureParentDirectories($path);
    }

    /**
     * @inheirtdoc
     */
    public function storeMiss(string $path): void
    {
        $this->cacheStorage->setCacheDataByKey($path, false);
        $this->autosave();
    }

    /**
     * @ingeritdoc
     */
    public function listContents(string $location, bool $deep = self::LIST_SHALLOW): DirectoryListing
    {
        $result = [];

        foreach ($this->cacheStorage->getCacheData() as $content) {
            if ($content === false) {
                continue;
            }
            $object = $content['type'] === 'file'
                ? new FileAttributes(
                    $content['path'],
                    $content['file_size'] ?? null,
                    $content['visibility'] ?? null,
                    $content['last_modified'] ?? null,
                    $content['mime_type'] ?? null,
                    $content['extra_metadata'] ?? [],
                )
                : new DirectoryAttributes(
                    $content['path'],
                    $content['visibility'] ?? null,
                    $content['last_modified'] ?? null
                );
            if (($content['type'] === 'file' && $content['dirname'] === $location) || $content['path'] === $location) {
                $result[] = $object;
            } elseif ($deep && $this->pathIsInDirectory($location, $content['path'])) {
                $result[] = $object;
            }
        }

        return new DirectoryListing($result);
    }

    /**
     * @inheritdoc
     */
    public function fileExists(string $location): bool
    {
        return $location !== false && $this->cacheStorage->getCacheDataByKey($location);
    }

    /**
     * @inheritdoc
     */
    public function read(string $location): string
    {
        $data = $this->cacheStorage->getCacheDataByKey($location);
        if ($data && isset($data['contents'])) {
            return (string)$data['contents'];
        }

        return '';
    }

    /**
     * @inheritdoc
     */
    public function readStream(string $location): void
    {
        return;
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newPath): void
    {
        if (!$this->fileExists($path)) {
            return;
        }

        $object = $this->cacheStorage->getCacheDataByKey($path);
        $this->cacheStorage->removeCacheDataByKey($path);
        $object['path'] = $newPath;
        $object = array_merge($object, $this->getPathInfo->execute($newPath));
        $this->cacheStorage->setCacheDataByKey($newPath, $object);
        $this->autosave();
    }

    /**
     * @inheritdoc
     */
    public function copy($path, $newpath): void
    {
        if ($this->fileExists($path)) {
            $object = $this->cacheStorage->getCacheDataByKey($path);
            $object = array_merge($object, $this->getPathInfo->execute($newpath));
            $this->updateObject($newpath, $object, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete($path): void
    {
        $this->storeMiss($path);
    }

    /**
     * @inheritdoc
     */
    public function deleteDir($dirname): void
    {
        foreach ($this->cacheStorage->getCacheData() as $path => $object) {
            if ($this->pathIsInDirectory($dirname, $path) || $path === $dirname) {
                $this->cacheStorage->removeCacheDataByKey($path);
            }
        }

        $this->cacheStorage->removeCompleteDataByKey($dirname);

        $this->autosave();
    }

    /**
     * @inheritdoc
     */
    public function mimeType($path): string
    {
        $cachedData = $this->cacheStorage->getCacheDataByKey($path);
        if (isset($cachedData['mimetype'])) {
            return $cachedData['mimetype'];
        }

        if (!$result = $this->read($path)) {
            return '';
        }

        $mimetype = $this->mimeTypeDetector->detectMimeType($path, $result);
        $cachedData['mimetype'] = $mimetype;
        $this->cacheStorage->setCacheDataByKey($path, $cachedData);

        return $mimetype ?: '';
    }

    /**
     * @inheritdoc
     */
    public function fileSize($path): int
    {
        return $this->cacheStorage->getCacheDataByKey($path)['file_size'] ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function lastModified($path): int
    {
        return $this->cacheStorage->getCacheDataByKey($path)['last_modified'] ?? 0;
    }

    /**
     * @inheritdoc
     */
    public function visibility(string $path): string
    {
        return $this->cacheStorage->getCacheDataByKey($path)['visibility'] ?? '';
    }

    /**
     * @inheritdoc
     */
    public function isComplete($dirname, $recursive): bool
    {
        if (!$this->cacheStorage->hasCompleteData($dirname)) {
            return false;
        }

        if ($recursive && $this->cacheStorage->getCompleteDataByKey($dirname) !== 'recursive') {
            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function setComplete($dirname, $recursive): void
    {
        $recursive = $recursive ? 'recursive' : true;
        $this->cacheStorage->setCompleteDataByKey($dirname, $recursive);
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        $this->cacheStorage->flushCache();
        $this->cacheStorage->flushComplete();
        $this->autosave();
    }

    /**
     * @inheritdoc
     */
    public function autosave(): void
    {
        if ($this->autoSave) {
            $this->save();
        }
    }

    /**
     * Ensure parent directories of an object.
     *
     * @param string $path
     */
    private function ensureParentDirectories(string $path)
    {
        $object = $this->cacheStorage->getCacheDataByKey($path);

        while ($object['dirname'] !== '' && !$this->cacheStorage->hasCacheData($object['dirname'])) {
            $object = $this->getPathInfo->execute($object['dirname']);
            $object['type'] = 'dir';
            $this->cacheStorage->setCacheDataByKey($object['path'], $object);
        }
    }

    /**
     * Determines if the path is inside the directory.
     *
     * @param string $directory
     * @param string $path
     *
     * @return bool
     */
    private function pathIsInDirectory(string $directory, string $path): bool
    {
        return $directory === '' || str_starts_with($path, $directory . '/');
    }
}
