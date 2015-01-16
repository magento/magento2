<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;

class WriteFactory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     */
    private $driverPool;

    /**
     * Constructor
     *
     * @param DriverPool $driverPool
     */
    public function __construct(DriverPool $driverPool)
    {
        $this->driverPool = $driverPool;
    }

    /**
     * Create a readable file.
     *
     * @param string $path
     * @param DriverInterface $driver
     * @param string $mode [optional]
     * @return Write
     */
    public function create($path, DriverInterface $driver, $mode = 'r')
    {
        return new Write($path, $driver, $mode);
    }

    /**
     * Create a readable file.
     *
     * @param string $path
     * @param string $driverCode
     * @param string $mode [optional]
     * @return Write
     */
    public function createWithDriverCode($path, $driverCode, $mode = 'r')
    {
        return new Write($path, $this->driverPool->getDriver($driverCode), $mode);
    }
}
