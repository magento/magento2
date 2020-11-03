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
     * Creates pre-configured driver.
     *
     * @param array $config
     * @param string $prefix
     * @return RemoteDriverInterface
     *
     * @throws DriverException
     */
    public function create(array $config, string $prefix): RemoteDriverInterface;
}
