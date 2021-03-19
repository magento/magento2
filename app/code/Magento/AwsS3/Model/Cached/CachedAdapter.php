<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Model\Cached;

use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use Magento\RemoteStorage\Driver\Cache\CacheInterface;
use Magento\RemoteStorage\Model\GetPathInfo;
use Psr\Log\LoggerInterface;

/**
 * Aws cached adapter model.
 */
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetPathInfo
     */
    private $getPathInfo;

    /**
     * @param FilesystemAdapter $adapter
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param GetPathInfo $getPathInfo
     * @throws FilesystemException
     */
    public function __construct(
        FilesystemAdapter $adapter,
        CacheInterface $cache,
        LoggerInterface $logger,
        GetPathInfo $getPathInfo
    ) {
        $this->adapter = $adapter;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->getPathInfo = $getPathInfo;
        $this->cache->load();
    }

    /**
     * @inheritdoc
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->adapter->write($path, $contents, $config);
        $result = [
            'type' => 'file',
            'path' => $path,
            'contents' => $contents,
        ];
        $result = array_merge($result, $this->adapter->fileSize($path)->jsonSerialize());
        $this->cache->updateObject($path, $result, true);
    }

    /**
     * @inheritdoc
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->adapter->writeStream($path, $contents, $config);
        $result = [
            'type' => 'file',
            'contents' => false,
        ];

        $this->cache->updateObject($path, $result, true);
    }

    /**
     * @inheritdoc
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($source, $destination, $config);
        $this->cache->rename($source, $destination);
    }

    /**
     * @inheritdoc
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($source, $destination, $config);
        $this->cache->copy($source, $destination);
    }

    /**
     * @inheritdoc
     */
    public function delete(string $path): void
    {
        $this->adapter->delete($path);
        $this->cache->delete($path);
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
        $this->cache->updateObject($path, ['path' => $path, 'type' => 'dir'], true);
    }

    /**
     * @inheritdoc
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($path, $visibility);
        $this->cache->updateObject($path, compact('path', 'visibility'), true);
    }

    /**
     * @inheritdoc
     */
    public function fileExists(string $path): bool
    {
        if ($this->cache->fileExists($path)) {
            return true;
        }

        if ($this->adapter->fileExists($path)) {
            $this->cache->updateObject($path, $this->adapter->fileSize($path)->jsonSerialize(), true);
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function read(string $path): string
    {
        $result = $this->cache->read($path);
        if ($result) {
            return $result;
        }

        $result = $this->adapter->read($path);
        $this->cache->updateObject($path, ['contents' => $result], true);

        return $result;
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
        if ($this->cache->isComplete($path, $deep)) {
            return $this->cache->listContents($path, $deep);
        }

        $contents = $this->adapter->listContents($path, $deep);
        $objects = [];
        while ($contents->valid()) {
            $objects[] = $contents->current();
            $contents->next();
        }
        if ($objects) {
            $this->cache->storeContents($path, $objects, $deep);
        }

        return $objects;
    }

    /**
     * @inheirtdoc
     */
    public function lastModified(string $path): FileAttributes
    {
        $result = $this->cache->lastModified($path);
        if ($result) {
            return new FileAttributes($path, null, null, $result);
        }

        $result = $this->adapter->lastModified($path);
        $object = $result->jsonSerialize() + compact('path');
        $this->cache->updateObject($path, $object, true);

        return $result;
    }

    /**
     * @inheirtdoc
     */
    public function fileSize(string $path): FileAttributes
    {
        $result = $this->cache->fileSize($path);
        if ($result) {
            return new FileAttributes($path, $result);
        }

        $result = $this->adapter->fileSize($path);
        $object = $result->jsonSerialize() + compact('path');
        $this->cache->updateObject($path, $object, true);

        return $result;
    }

    /**
     * @inheirtdoc
     */
    public function mimeType(string $path): FileAttributes
    {
        $result = $this->cache->mimeType($path);
        if ($result) {
            return new FileAttributes($path, null, null, null, $result);
        }

        $result = $this->adapter->mimeType($path);
        $object = $result->jsonSerialize() + compact('path');
        $this->cache->updateObject($path, $object, true);

        return $result;
    }

    /**
     * @inheirtdoc
     */
    public function visibility(string $path): FileAttributes
    {
        $result = $this->cache->visibility($path);
        if ($result) {
            return new FileAttributes($path, null, $result);
        }

        $result = $this->adapter->visibility($path);
        $object = $result->jsonSerialize() + compact('path');
        $this->cache->updateObject($path, $object, true);

        return $result;
    }

    /**
     * Get file metadata.
     *
     * @deplacated There is no getMetadata() method in FilesystemAdapter anymore.
     * https://flysystem.thephpleague.com/v2/docs/advanced/upgrade-to-2.0.0/
     * Added for compatibility with Magento/AwsS3/Driver/AwsS3::getMetadata()
     *
     * @param string $path
     * @return array
     * @throws FilesystemException
     */
    public function getMetadata(string $path): array
    {
        $fileAttributes = $this->adapter->fileSize($path)->jsonSerialize();
        $width = isset($fileAttributes['extra_metadata']['Metadata']['image-width'])
            ? (int)$fileAttributes['extra_metadata']['Metadata']['image-width'] : 0;
        $height = isset($fileAttributes['extra_metadata']['Metadata']['image-height'])
            ? (int)$fileAttributes['extra_metadata']['Metadata']['image-height'] : 0;
        $pathInfo = $this->getPathInfo->execute($fileAttributes['path']);

        return [
            'path' => $pathInfo['path'],
            'dirname' => $pathInfo['dirname'],
            'basename' => $pathInfo['basename'],
            'extension' => $pathInfo['extension'],
            'filename' => $pathInfo['filename'],
            'metadata' => [
                'image-width' => $width,
                'image-height' => $height,
            ],
        ];
    }
}
