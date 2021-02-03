<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\FilesystemAdapter;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;

/**
 * Metadata provider for filesystem storage.
 */
class MetadataProvider implements MetadataProviderInterface
{
    /**
     * @var FilesystemAdapter
     */
    private $adapter;

    /**
     * @var Cache\CacheInterface
     */
    private $cache;

    /**
     * MetadataProvider constructor.
     *
     * @param FilesystemAdapter $adapter
     * @param Cache\CacheInterface $cache
     */
    public function __construct(
        FilesystemAdapter $adapter,
        CacheInterface $cache
    ) {
        $this->adapter = $adapter;
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(string $path): array
    {
        $metadata = $this->cache->getMetadata($path);
        if ($metadata && is_array($metadata) && $this->isMetadataComplete($metadata)) {
            return $metadata;
        }
        $meta = $this->adapter->lastModified($path);
        $data = [
            'path' => $path,
            'type' => $meta->type(),
            'size' => $meta->fileSize(),
            'timestamp' => $meta->lastModified(),
            'visibility' => $meta->visibility(),
            'mimetype' => $meta->mimeType(),
            'dirname' => dirname($meta->path()),
            'basename' => basename($meta->path()),
            'extra' => $meta->extraMetadata(),
        ];
        $this->cache->updateMetadata($path, $data, true);
        return $data;
    }

    /**
     * Check is the metadata structure complete.
     *
     * @param array $metadata
     * @return bool
     */
    private function isMetadataComplete($metadata)
    {
        $keys = ['type', 'size', 'timestamp', 'visibility', 'mimetype', 'dirname'. 'basename', 'extra'];
        foreach ($keys as $key) {
            if (!isset($metadata[$key])) {
                return false;
            }
        }
        return true;
    }
}
