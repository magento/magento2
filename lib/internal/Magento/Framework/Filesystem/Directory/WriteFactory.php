<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\DriverPool;

/**
 * Class \Magento\Framework\Filesystem\Directory\WriteFactory
 *
 * @since 2.0.0
 */
class WriteFactory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     * @since 2.0.0
     */
    private $driverPool;

    /**
     * Constructor
     *
     * @param DriverPool $driverPool
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create($path, $driverCode = DriverPool::FILE, $createPermissions = null)
    {
        $driver = $this->driverPool->getDriver($driverCode);
        $factory = new \Magento\Framework\Filesystem\File\WriteFactory($this->driverPool);
        return new Write($factory, $driver, $path, $createPermissions);
    }
}
