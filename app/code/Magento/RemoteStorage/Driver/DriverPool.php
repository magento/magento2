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
    public const PATH_PREFIX = 'remote_storage/prefix';
    public const PATH_CONFIG = 'remote_storage/config';

    /**
     * Driver name.
     */
    public const REMOTE = 'remote';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DriverFactoryPool
     */
    private $driverFactoryPool;

    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @var array
     */
    private $pool = [];

    /**
     * @param Config $config
     * @param DriverFactoryPool $driverFactoryPool
     * @param BaseDriverPool $driverPool
     */
    public function __construct(
        Config $config,
        DriverFactoryPool $driverFactoryPool,
        BaseDriverPool $driverPool
    ) {
        $this->config = $config;
        $this->driverFactoryPool = $driverFactoryPool;
        $this->driverPool = $driverPool;
    }

    /**
     * Retrieves remote driver.
     *
     * @param string $code
     * @return DriverInterface
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

            if ($driver && $this->driverFactoryPool->has($driver)) {
                return $this->pool[$code] = $this->driverFactoryPool->get($driver)->create(
                    $this->config->getConfig(),
                    $this->config->getPrefix()
                );
            }

            throw new RuntimeException(__('Remote driver is not available.'));
        }

        return $this->driverPool->getDriver($code);
    }
}
