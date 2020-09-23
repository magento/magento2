<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Factory for drivers with additional configuration.
 */
interface DriverFactoryInterface
{
    /**
     * Creates pre-configured driver.
     *
     * @return DriverInterface
     */
    public function create(): DriverInterface;
}
