<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem;

/**
 * A pool of stream wrappers.
 *
 * @api
 */
interface DriverPoolInterface
{
    /**
     * Gets a driver instance by code
     *
     * @param string $code
     * @return DriverInterface
     */
    public function getDriver($code): DriverInterface;
}
