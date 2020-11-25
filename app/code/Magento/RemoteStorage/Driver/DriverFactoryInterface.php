<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

/**
 * Factory for drivers with additional configuration.
 */
interface DriverFactoryInterface
{
    /**
     * Creates driver from stored config.
     *
     * @return RemoteDriverInterface
     *
     * @throws DriverException
     */
    public function create(): RemoteDriverInterface;

    /**
     * Creates driver from config.
     *
     * @param array $config
     * @param string $prefix
     * @param string $cacheAdapter
     * @param array $cacheConfig
     * @return RemoteDriverInterface
     *
     * @throws DriverException
     */
    public function createConfigured(
        array $config,
        string $prefix,
        string $cacheAdapter,
        array $cacheConfig
    ): RemoteDriverInterface;
}
