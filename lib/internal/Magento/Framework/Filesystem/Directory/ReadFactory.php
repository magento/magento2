<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Filesystem\DriverPool;

/**
 * Class \Magento\Framework\Filesystem\Directory\ReadFactory
 *
 * @since 2.0.0
 */
class ReadFactory
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
     * Create a readable directory
     *
     * @param string $path
     * @param string $driverCode
     * @return ReadInterface
     * @since 2.0.0
     */
    public function create($path, $driverCode = DriverPool::FILE)
    {
        $driver = $this->driverPool->getDriver($driverCode);
        $factory = new \Magento\Framework\Filesystem\File\ReadFactory($this->driverPool);
        return new Read($factory, $driver, $path);
    }
}
