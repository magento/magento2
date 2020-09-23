<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool as BaseDriverPool;
use Magento\Framework\Filesystem\DriverPoolInterface;

/**
 * The remote driver pool.
 */
class DriverPool implements DriverPoolInterface
{
    public const PATH_DRIVER = 'system/file_system/driver';
    public const REMOTE = 'remote';

    /**
     * @var ScopeConfigInterface
     */
    private $config;

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
     * @param BaseDriverPool $driverPool
     * @param ScopeConfigInterface $config
     * @param array $remotePool
     */
    public function __construct(BaseDriverPool $driverPool, ScopeConfigInterface $config, array $remotePool = [])
    {
        $this->driverPool = $driverPool;
        $this->config = $config;
        $this->remotePool = $remotePool;
    }

    /**
     * @inheritDoc
     */
    public function getDriver($code = self::REMOTE): DriverInterface
    {
        $driver = $this->config->getValue('system/file_system/driver');

        if (isset($this->pool[$code])) {
            return $this->pool[$code];
        }

        if ($driver && $driver !== BaseDriverPool::FILE) {
            return $this->pool[$code] = $this->remotePool[$driver]->create();
        }

        return $this->pool[$code] = $this->driverPool->getDriver($code);
    }
}
