<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Mftf\Helper;

/**
 * Cache mock for metadata provider.
 */
class DummyMetadataCache implements \Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface
{
    /**
     * @inheirtDoc
     */
    public function exists(string $path): ?bool
    {
        return null;
    }

    /**
     * @inheirtDoc
     */
    public function getMetadata(string $path): ?array
    {
        return null;
    }

    /**
     * @inheirtDoc
     */
    public function flushCache(): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function purgeQueue(): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function moveFile(string $path, string $newpath): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function copyFile(string $path, string $newpath): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function deleteFile(string $path): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function deleteDir(string $dirname): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function updateMetadata(string $path, array $objectMetadata, bool $persist = false): void
    {
    }

    /**
     * @inheirtDoc
     */
    public function storeFileNotExists(string $path): void
    {
    }
}
