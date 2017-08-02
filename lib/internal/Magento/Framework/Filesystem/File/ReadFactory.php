<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;

/**
 * Class \Magento\Framework\Filesystem\File\ReadFactory
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
     * Create a readable file
     *
     * @param string $path
     * @param DriverInterface|string $driver Driver or driver code
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @since 2.0.0
     */
    public function create($path, $driver)
    {
        if (is_string($driver)) {
            return new Read($path, $this->driverPool->getDriver($driver));
        }
        return new Read($path, $driver);
    }
}
