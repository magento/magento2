<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\DriverPoolInterface;

/**
 * The factory of the filesystem directory instances for write operations.
 */
class WriteFactory
{
    /**
     * Pool of filesystem drivers
     *
     * @var DriverPoolInterface
     */
    private $driverPool;

    /**
     * Constructor
     *
     * @param DriverPoolInterface $driverPool
     */
    public function __construct(DriverPoolInterface $driverPool)
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
        $factory = new \Magento\Framework\Filesystem\File\WriteFactory(
            $this->driverPool
        );

        return new Write(
            $factory,
            $driver,
            $path,
            $createPermissions,
            new PathValidator($driver)
        );
    }
}
