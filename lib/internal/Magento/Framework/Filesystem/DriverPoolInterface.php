<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
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
