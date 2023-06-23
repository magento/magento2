<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter\Cache;

use Magento\Framework\App\CacheInterface as MagentoCacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\RemoteStorage\Driver\Adapter\PathUtil;

/**
 * Generic cache implementation for filesystem storage.
 */
class Generic implements CacheInterface
{
    /**
     * @var array
     */
    private $cacheData = [];

    /**
     * List of cache paths to be purged when persist is called
     *
     * @var array
     */
    private $cachePathPurgeQueue = [];

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var MagentoCacheInterface
     */
    private $cacheAdapter;

    /**
     * @var PathUtil
     */
    private $pathUtil;

    /**
     * @param MagentoCacheInterface $cacheAdapter
     * @param SerializerInterface $serializer
     * @param PathUtil $pathUtil
     * @param string $prefix
     */
    public function __construct(
        MagentoCacheInterface $cacheAdapter,
        SerializerInterface $serializer,
        PathUtil $pathUtil,
        $prefix = 'flysystem:'
    ) {
        $this->prefix = $prefix;
        $this->serializer = $serializer;
        $this->cacheAdapter = $cacheAdapter;
        $this->pathUtil = $pathUtil;
    }

    /**
     * @inheritdoc
     */
    public function purgeQueue(): void
    {
        foreach ($this->cachePathPurgeQueue as $path) {
            unset($this->cacheData[$path]);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateMetadata(string $path, array $objectMetadata, bool $persist = false): void
    {
        $this->cacheData[$path] = array_merge($this->pathUtil->pathInfo($path), $objectMetadata);
        $this->ensureParentDirectories($path);
    }

    /**
     * @inheritdoc
     */
    public function storeFileNotExists(string $path): void
    {
        $this->cacheData[$path] = false;
    }

    /**
     * @inheritdoc
     */
    public function exists(string $path): ?bool
    {
        if (!isset($this->cacheData[$path])) {
            return null;
        }

        return array_key_exists($path, $this->cacheData) ? $this->cacheData[$path] !== false : null;
    }

    /**
     * @inheritdoc
     */
    public function moveFile(string $path, string $newpath): void
    {
        if ($this->exists($path)) {
            $object = $this->cacheData[$path];
            unset($this->cacheData[$path]);
            $this->cachePathPurgeQueue[] = $path;
            $object['path'] = $newpath;
            $object = array_merge($object, $this->pathUtil->pathInfo($newpath));
            $this->cacheData[$newpath] = $object;
            $this->purgeQueue();
        }
    }

    /**
     * @inheritdoc
     */
    public function copyFile(string $path, string $newpath): void
    {
        if ($this->exists($path)) {
            $object = $this->cacheData[$path];
            $object = array_merge($object, $this->pathUtil->pathInfo($newpath));
            $this->updateMetadata($newpath, $object, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteFile(string $path): void
    {
        $this->storeFileNotExists($path);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function deleteDir(string $dirname): void
    {
        foreach ($this->cacheData as $path => $object) {
            if ($this->pathIsInDirectory($dirname, $path) || $path === $dirname) {
                unset($this->cacheData[$path]);
                $this->cachePathPurgeQueue[] = $path;
            }
        }

        $this->purgeQueue();
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(string $path): ?array
    {
        if (isset($this->cacheData[$path]['type'])) {
            return $this->cacheData[$path];
        } else {
            return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function flushCache(): void
    {
        $this->cacheData = [];
    }

    /**
     * Load from serialized cache data.
     *
     * @param string $json
     */
    private function setFromStorage(string $json)
    {
        $this->cacheData = array_merge($this->cacheData, $this->serializer->unserialize($json));
    }

    /**
     * Ensure parent directories of an object.
     *
     * @param string $path object path
     */
    private function ensureParentDirectories($path)
    {
        $object = $this->cacheData[$path];

        while ($object['dirname'] !== '' && ! isset($this->cacheData[$object['dirname']])) {
            $object = $this->pathUtil->pathInfo($object['dirname']);
            $object['type'] = 'dir';
            $this->cacheData[$object['path']] = $object;
        }
    }

    /**
     * Determines if the path is inside the directory.
     *
     * @param string $directory
     * @param string $path
     * @return bool
     */
    private function pathIsInDirectory($directory, $path)
    {
        return $directory === '' || strpos((string)$path, $directory . '/') === 0;
    }
}
