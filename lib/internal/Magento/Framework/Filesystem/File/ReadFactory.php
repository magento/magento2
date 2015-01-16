<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;

class ReadFactory
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
     * Create a readable file
     *
     * @param string $path
     * @param DriverInterface $driver
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    public function create($path, DriverInterface $driver = null)
    {
        return new Read($path, $driver);
    }

    /**
     * Create a readable file
     *
     * @param string $path
     * @param string|null $driverCode
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    public function createWithDriverCode($path, $driverCode)
    {
        return new Read($path, $this->driverPool->getDriver($driverCode));
    }
}
