<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\FilesystemAdapter;
use Magento\Framework\App\ObjectManager;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;

/**
 * Factory for MetadataProviderInterface.
 */
class MetadataProviderFactory implements MetadataProviderFactoryInterface
{
    /**
     * @inheritdoc
     */
    public function create(FilesystemAdapter $adapter, CacheInterface $cache): MetadataProviderInterface
    {
        return ObjectManager::getInstance()->create(MetadataProviderInterface::class, [
            'adapter' => $adapter,
            'cache' => $cache,
        ]);
    }
}
