<?php
namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\FilesystemAdapter;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;

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
}
