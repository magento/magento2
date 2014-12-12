<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Filesystem\Directory;

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
     * Create a readable directory
     *
     * @param string $path
     * @param string $protocol
     * @return ReadInterface
     */
    public function create($path, $protocol = DriverPool::FILE)
    {
        $driver = $this->driverPool->getDriver($protocol);
        $factory = new \Magento\Framework\Filesystem\File\ReadFactory($this->driverPool);
        return new Read($factory, $driver, $path);
    }
}
