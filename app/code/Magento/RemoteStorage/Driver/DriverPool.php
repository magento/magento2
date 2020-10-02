<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool as BaseDriverPool;
use Magento\Framework\Filesystem\DriverPoolInterface;
use Magento\RemoteStorage\Model\Config;

/**
 * The remote driver pool.
 */
class DriverPool implements DriverPoolInterface
{
    public const PATH_DRIVER = 'remote_storage/driver';
    public const PATH_IS_PUBLIC = 'remote_storage/is_public';
    public const REMOTE = 'remote';

    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @var DriverInterface[]
     */
    private $pool = [];

    /**
     * @var DriverFactoryInterface[]
     */
    private $remotePool;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param BaseDriverPool $driverPool
     * @param Config $config
     * @param array $remotePool
     */
    public function __construct(BaseDriverPool $driverPool, Config $config, array $remotePool = [])
    {
        $this->driverPool = $driverPool;
        $this->config = $config;
        $this->remotePool = $remotePool;
    }

    /**
     * Retrieves remote driver.
     *
     * @param string $code
     * @return DriverInterface
     *
     * @throws RuntimeException
     * @throws FileSystemException
     */
    public function getDriver($code = self::REMOTE): DriverInterface
    {
        if ($code === self::REMOTE) {
            if (isset($this->pool[$code])) {
                return $this->pool[$code];
            }

            $driver = $this->config->getDriver();

            if ($driver && isset($this->remotePool[$driver])) {
                return $this->pool[$code] = $this->remotePool[$driver]->create();
            }

            throw new RuntimeException(__('Remote driver is not available.'));
        }

        return $this->driverPool->getDriver($code);
    }
}
