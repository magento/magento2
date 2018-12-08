<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;

<<<<<<< HEAD
=======
/**
 * Opens a file for reading and/or writing
 * @api
 */
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
class WriteFactory extends ReadFactory
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
        parent::__construct($driverPool);
        $this->driverPool = $driverPool;
    }

    /**
     * Create a {@see WriterInterface}
     *
     * @param string $path
     * @param DriverInterface|string $driver Driver or driver code
     * @param string $mode [optional]
     * @return WriteInterface
     */
    public function create($path, $driver, $mode = 'r')
    {
        if (is_string($driver)) {
            return new Write($path, $this->driverPool->getDriver($driver), $mode);
        }
        return new Write($path, $driver, $mode);
    }
}
