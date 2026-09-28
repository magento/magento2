<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Mftf\Helper;

/**
 * Cache mock for metadata provider.
 */
class DummyMetadataCache implements \Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface
{
    /**
     * @inheritDoc
     */
    public function exists(string $path): ?bool
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata(string $path): ?array
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function flushCache(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function purgeQueue(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function moveFile(string $path, string $newpath): void
    {
    }

    /**
     * @inheritDoc
     */
    public function copyFile(string $path, string $newpath): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteFile(string $path): void
    {
    }

    /**
     * @inheritDoc
     */
    public function deleteDir(string $dirname): void
    {
    }

    /**
     * @inheritDoc
     */
    public function updateMetadata(string $path, array $objectMetadata, bool $persist = false): void
    {
    }

    /**
     * @inheritDoc
     */
    public function storeFileNotExists(string $path): void
    {
    }
}
