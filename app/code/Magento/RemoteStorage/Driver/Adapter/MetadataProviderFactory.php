<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Adapter;

use League\Flysystem\FilesystemAdapter;
use Magento\Framework\ObjectManagerInterface;
use Magento\RemoteStorage\Driver\Adapter\Cache\CacheInterface;

/**
 * Factory for MetadataProviderInterface.
 */
class MetadataProviderFactory implements MetadataProviderFactoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function create(FilesystemAdapter $adapter, CacheInterface $cache): MetadataProviderInterface
    {
        return $this->objectManager->create(MetadataProviderInterface::class, [
            'adapter' => $adapter,
            'cache' => $cache,
        ]);
    }
}
