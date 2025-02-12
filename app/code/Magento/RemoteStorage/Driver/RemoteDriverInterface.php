<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\Framework\Filesystem\ExtendedDriverInterface;

/**
 * Remote storage driver.
 * @api
 */
interface RemoteDriverInterface extends ExtendedDriverInterface
{
    /**
     * Test storage connection.
     *
     * @throws DriverException
     */
    public function test(): void;
}
