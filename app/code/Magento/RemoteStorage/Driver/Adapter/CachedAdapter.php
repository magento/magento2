<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;

/**
 * Cached adapter implementation for filesystem storage.
 */
class CachedAdapter implements CachedAdapterInterface
{
    /**
     * @var FilesystemAdapter
     */
    private $adapter;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var MetadataProviderInterface
     */
    private $metadataProvider;

    /**
     * Constructor.
     *
     * @param FilesystemAdapter $adapter
     * @param CacheInterface $cache
     * @param MetadataProviderInterface $metadataProvider
     */
    public function __construct(
        FilesystemAdapter $adapter,
        CacheInterface $cache,
        MetadataProviderInterface $metadataProvider
    ) {
        $this->adapter = $adapter;
        $this->cache = $cache;
        $this->metadataProvider = $metadataProvider;
    }

    /**
     * @inheritdoc
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->adapter->write($path, $contents, $config);
        $object = [
            'type' => 'file',
            'path' => $path,
        ];
        $this->cache->updateMetadata($path, $object, true);
    }

    /**
     * @inheritdoc
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->adapter->writeStream($path, $contents, $config);
        $object = [
            'type' => 'file',
            'path' => $path,
        ];
        $this->cache->updateMetadata($path, $object, true);
    }

    /**
     * @inheritdoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($source, $destination, $config);
        $this->cache->moveFile($source, $destination);
    }

    /**
     * @inheritdoc
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($source, $destination, $config);
        $this->cache->copyFile($source, $destination);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): void
    {
        $this->adapter->delete($path);
        $this->cache->deleteFile($path);
    }

    /**
     * @inheritdoc
     */
    public function deleteDirectory(string $path): void
    {
        $this->adapter->deleteDirectory($path);
        $this->cache->deleteDir($path);
    }

    /**
     * @inheritdoc
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($path, $config);
        $type = 'dir';
        $dirname = $path;
        $this->cache->updateMetadata($dirname, ['path' => $path, 'type' => $type], true);
    }

    /**
     * @inheritdoc
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);
        $this->cache->updateMetadata($path, ['path' => $path, 'visibility' => $visibility], true);
    }

    /**
     * @inheritdoc
     */
    public function fileExists(string $path): bool
    {
        $cacheHas = $this->cache->exists($path);

        if ($cacheHas !== null) {
            return $cacheHas;
        }

        $exists = $this->adapter->fileExists($path);

        if (!$exists) {
            try {
                // check if target is a directory
                $exists = iterator_count($this->adapter->listContents($path, false)) > 0;
            } catch (\Throwable $e) {
                // catch closed iterator
                $exists = false;
            }
        }

        if (!$exists) {
            $this->cache->storeFileNotExists($path);
        } else {
            $cacheEntry = is_array($exists) ? $exists : ['path' => $path];
            $this->cache->updateMetadata($path, $cacheEntry, true);
        }

        return $exists;
    }

    /**
     * @inheritdoc
     */
    public function read(string $path): string
    {
        return $this->adapter->read($path);
    }

    /**
     * @inheritdoc
     */
    public function readStream(string $path)
    {
        return $this->adapter->readStream($path);
    }

    /**
     * @inheritdoc
     */
    public function listContents(string $path, bool $deep): iterable
    {
        return $this->adapter->listContents($path, $deep);
    }

    /**
     * @inheritdoc
     */
    public function fileSize(string $path): FileAttributes
    {
        $result = $this->metadataProvider->getMetadata($path);
        return new FileAttributes($path, (int)$result['size']);
    }

    /**
     * @inheritdoc
     */
    public function mimeType(string $path): FileAttributes
    {
        $result = $this->metadataProvider->getMetadata($path);
        return new FileAttributes($path, null, null, null, $result['mimetype']);
    }

    /**
     * @inheritdoc
     */
    public function lastModified(string $path): FileAttributes
    {
        $result = $this->metadataProvider->getMetadata($path);
        return new FileAttributes($path, null, null, (int)$result['timestamp']);
    }

    /**
     * @inheritdoc
     */
    public function visibility(string $path): FileAttributes
    {
        $result = $this->metadataProvider->getMetadata($path);
        return new FileAttributes($path, null, $result['visibility']);
    }

    /**
     * @inheritdoc
     */
    public function directoryExists(string $path): bool
    {
        return $this->adapter->directoryExists($path);
    }
}
