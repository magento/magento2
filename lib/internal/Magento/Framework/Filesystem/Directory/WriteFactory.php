<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

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
     * Create a writable directory
     *
     * @param string $path
     * @param string $driverCode
     * @param int $createPermissions
     * @return \Magento\Framework\Filesystem\Directory\Write
     */
    public function create($path, $driverCode = DriverPool::FILE, $createPermissions = null)
    {
        $driver = $this->driverPool->getDriver($driverCode);
        $factory = new \Magento\Framework\Filesystem\File\WriteFactory($this->driverPool);
        return new Write($factory, $driver, $path, $createPermissions);
    }
}
