<?php

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToRetrieveMetadata;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;

class CachedAdapter implements FilesystemAdapter
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
     * Constructor.
     *
     * @param FilesystemAdapter $adapter
     * @param CacheInterface   $cache
     */
    public function __construct(FilesystemAdapter $adapter, CacheInterface $cache)
    {
        $this->adapter = $adapter;
        $this->cache = $cache;
        $this->cache->load();
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->adapter->write($path, $contents, $config);
        $result['type'] = 'file';
        $this->cache->updateMetadata($path, $result + compact('path', 'contents'), true);
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->adapter->writeStream($path, $contents, $config);
        $result['type'] = 'file';
        $contents = false;
        $this->cache->updateMetadata($path, $result + compact('path', 'contents'), true);
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($source, $destination, $config);
        $this->cache->moveFile($source, $destination);
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($source, $destination, $config);
        $this->cache->copyFile($source, $destination);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $path): void
    {
        $this->adapter->delete($path);
        $this->cache->deleteFile($path);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $path): void
    {
        $this->adapter->deleteDirectory($path);
        $this->cache->deleteDir($path);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($path, $config);
        $type = 'dir';
        $dirname = $path;
        $this->cache->updateMetadata($dirname, compact('path', 'type'), true);
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);
        $this->cache->updateMetadata($path, compact('path', 'visibility'), true);
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists(string $path): bool
    {
        $cacheHas = $this->cache->exists($path);

        if ($cacheHas) {
            return $cacheHas;
        }

        $adapterResponse = $this->adapter->fileExists($path);

        if (! $adapterResponse) {
            $this->cache->resetData($path);
        } else {
            $cacheEntry = is_array($adapterResponse) ? $adapterResponse : compact('path');
            $this->cache->updateMetadata($path, $cacheEntry, true);
        }

        return $adapterResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        $result = $this->cache->getFileData($path);
        if ($result !== false) {
            return $result;
        }
        $result = $this->adapter->read($path);
        if ($result) {
            $object = ['contents' => $result] + compact('path');
            $this->cache->updateMetadata($path, $object, true);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream(string $path)
    {
        return $this->adapter->readStream($path);
    }

    /**
     * {@inheritdoc}
     */
    public function listContents(string $path, bool $deep): iterable
    {
        return $this->adapter->listContents($path, $deep);
    }

    /**
     * {@inheritdoc}
     */
    public function isFile($path)
    {
        $metadata = $this->getMetadata($path);
        if ($metadata) {
            return isset($metadata['type']) && $metadata['type'] === 'file';
        }
        return false;
    }

    public function getMetadata($path)
    {
        $metadata = $this->cache->getMetadata($path);
        if ($metadata && is_array($metadata)) {
            return $metadata;
        }
        $meta = $this->adapter->fileSize($path);
        $object = [
            'type' => $meta->type(),
            'size' => $meta->fileSize(),
            'timestamp' => $meta->lastModified(),
            'visibility' => $meta->visibility(),
            'mimetype' => $meta->mimeType(),
            'dirname' => dirname($meta->path()),
            'basename' => basename($meta->path()),
            'extra' => $meta->extraMetadata(),
        ];
        $this->cache->updateMetadata($path, $object + compact('path'), true);
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function fileSize(string $path): FileAttributes
    {
        $result = $this->getMetadata($path);
        if (!isset($result['size'])) {
            throw UnableToRetrieveMetadata::fileSize($path);
        }
        return new FileAttributes($path, (int)$result['size']);
    }

    /**
     * {@inheritdoc}
     */
    public function mimeType(string $path): FileAttributes
    {
        $result = $this->getMetadata($path);
        if (!isset($result['mimetype'])) {
            throw UnableToRetrieveMetadata::mimeType($path);
        }
        return new FileAttributes($path, null, null, null, $result['mimetype']);
    }

    /**
     * {@inheritdoc}
     */
    public function lastModified(string $path): FileAttributes
    {
        $result = $this->getMetadata($path);
        if (!isset($result['timestamp'])) {
            throw UnableToRetrieveMetadata::lastModified($path);
        }
        return new FileAttributes($path, null, null, (int)$result['timestamp']);
    }

    /**
     * {@inheritdoc}
     */
    public function visibility(string $path): FileAttributes
    {
        $result = $this->getMetadata($path);
        if (!isset($result['visibility'])) {
            throw UnableToRetrieveMetadata::visibility($path);
        }
        return new FileAttributes($path, null, $result['visibility']);
    }
}
