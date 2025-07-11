<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToRetrieveMetadata;
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
     * Check is the given path an existing directory.
     *
     * @param string $path
     * @return bool
     */
    private function isDirectory($path): bool
    {
        try {
            return $this->adapter->directoryExists($path);
        } catch (\Throwable $e) {
            // catch closed iterator
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function getMetadata(string $path): array
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $metadata = $this->cache->getMetadata($path);
        if (isset($metadata['type']) && ($metadata['type'] == 'dir' || $this->isMetadataComplete($metadata))) {
            return $metadata;
        }
        try {
            $meta = $this->adapter->lastModified($path);
        } catch (UnableToRetrieveMetadata $e) {
            if ($this->isDirectory($path)) {
                $data = [
                    'path' => $path,
                    'type' => 'dir',
                    'size' => null,
                    'timestamp' => null,
                    'visibility' => null,
                    'mimetype' => null,
                    'dirname' => dirname($path),
                    'basename' => basename($path),
                ];
                $this->cache->updateMetadata($path, $data, true);
                return $data;
            } else {
                throw new UnableToRetrieveMetadata(
                    "Unable to retrieve metadata for file at location: {$path}. {$e->getMessage()}",
                    0,
                    $e
                );
            }
        } catch (\InvalidArgumentException | FilesystemException $e) {
            throw new UnableToRetrieveMetadata(
                "Unable to retrieve metadata for file at location: {$path}. {$e->getMessage()}",
                0,
                $e
            );
        }
        $data = [
            'path' => $path,
            'type' => $meta->type(),
            'size' => $meta->fileSize(),
            'timestamp' => $meta->lastModified(),
            'visibility' => $meta->visibility(),
            'mimetype' => $meta->mimeType(),
            'dirname' => dirname($meta->path()),
            'basename' => basename($meta->path(), '.' . pathinfo($path, PATHINFO_EXTENSION)),
        ];
        // phpcs:enable Magento2.Functions.DiscouragedFunction
        $extraMetadata = $meta->extraMetadata();
        if (isset($extraMetadata['Metadata']['image-width']) && isset($extraMetadata['Metadata']['image-height'])) {
            $data['extra'] = $extraMetadata['Metadata'];
        }
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
        $keys = ['type', 'size', 'timestamp', 'visibility', 'mimetype', 'dirname', 'basename'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $metadata)) {
                return false;
            }
        }
        return true;
    }
}
