<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Remote storage driver.
 */
interface RemoteDriverInterface extends DriverInterface
{
    /**
     * Test storage connection.
     *
     * @throws DriverException
     */
    public function test(): void;
}
