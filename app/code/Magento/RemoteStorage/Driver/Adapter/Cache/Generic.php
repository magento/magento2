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
     * Destructor.
     * @deprecated
     */
    public function __destruct()
    {
        $this->persist();
    }

    /**
     * @inheritdoc
     */
    public function load(): void
    {
        $contents = $this->cacheAdapter->load($this->prefix);
        if ($contents !== false) {
            $this->setFromStorage($contents);
        }
    }

    /**
     * @inheritdoc
     */
    public function persist(): void
    {
        $contents = $this->getForStorage();
        $this->cacheAdapter->save($contents, $this->prefix);
    }

    /**
     * @inheritdoc
     */
    public function updateMetadata(string $path, array $objectMetadata, bool $persist = false): void
    {
        if (!$this->exists($path)) {
            $this->cacheData[$path] = $this->pathUtil->pathInfo($path);
        }

        $this->cacheData[$path] = array_merge($this->cacheData[$path], $objectMetadata);

        if ($persist) {
            $this->persist();
        }

        $this->ensureParentDirectories($path);
    }

    /**
     * @inheritdoc
     */
    public function resetData(string $path): void
    {
        $this->cacheData[$path] = false;
        $this->persist();
    }

    /**
     * @inheritdoc
     */
    public function exists(string $path): bool
    {
        if ($path !== false && isset($this->cacheData[$path])) {
            return $this->cacheData[$path] !== false;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getFileContents(string $path): ?array
    {
        if (isset($this->cacheData[$path]['contents']) && $this->cacheData[$path]['contents'] !== false) {
            return $this->cacheData[$path];
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function moveFile(string $path, string $newpath): void
    {
        if ($this->exists($path)) {
            $object = $this->cacheData[$path];
            unset($this->cacheData[$path]);
            $object['path'] = $newpath;
            $object = array_merge($object, $this->pathUtil->pathInfo($newpath));
            $this->cacheData[$newpath] = $object;
            $this->persist();
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
        $this->resetData($path);
    }

    /**
     * @inheritdoc
     */
    public function deleteDir(string $dirname): void
    {
        foreach ($this->cacheData as $path => $object) {
            if ($this->pathIsInDirectory($dirname, $path) || $path === $dirname) {
                unset($this->cacheData[$path]);
            }
        }

        $this->persist();
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(string $path): ?array
    {
        if (isset($this->cacheData[$path]['type'])) {
            return $this->cacheData[$path];
        }

        return null;
    }

    /**
     * Filter data to store in the cache.
     *
     * @param array $objectListing
     * @return array
     */
    private function filterData(array $objectListing)
    {
        $cachedProperties = array_flip([
            'path',
            'size',
            'type',
            'timestamp',
            'visibility',
            'mimetype',
            'basename',
            'dirname',
            'extra'
        ]);

        foreach ($objectListing as $path => $object) {
            if (is_array($object)) {
                $objectListing[$path] = array_intersect_key($object, $cachedProperties);
            }
        }

        return $objectListing;
    }

    /**
     * @inheritdoc
     */
    public function flushCache(): void
    {
        $this->cacheData = [];
        $this->persist();
    }

    /**
     * Retrieve serialized cache data.
     *
     * @return string
     */
    private function getForStorage()
    {
        return $this->serializer->serialize([$this->filterData($this->cacheData)]);
    }

    /**
     * Load from serialized cache data.
     *
     * @param string $json
     */
    private function setFromStorage($json)
    {
        $this->cacheData = $this->serializer->unserialize($json);
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
