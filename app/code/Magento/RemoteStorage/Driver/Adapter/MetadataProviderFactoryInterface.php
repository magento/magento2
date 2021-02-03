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
 * Interface for MetadataProvider factory.
 */
interface MetadataProviderFactoryInterface
{
    /**
     * Create instance of metadata provider.
     *
     * @param FilesystemAdapter $adapter
     * @param CacheInterface $cache
     * @return MetadataProviderInterface
     */
    public function create(FilesystemAdapter $adapter, CacheInterface $cache): MetadataProviderInterface;
}
