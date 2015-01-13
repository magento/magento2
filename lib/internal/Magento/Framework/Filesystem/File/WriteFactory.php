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
     * @param string|null $protocol [optional]
     * @param DriverInterface $driver [optional]
     * @param string $mode [optional]
     * @return Write
     * @throws \InvalidArgumentException
     */
    public function create($path, $protocol = null, DriverInterface $driver = null, $mode = 'r')
    {
        if ($protocol) {
            $driver = $this->driverPool->getDriver($protocol);
        } elseif (!$driver) {
            throw new \InvalidArgumentException('Either driver or protocol must be specified.');
        }
        return new Write($path, $driver, $mode);
    }
}
