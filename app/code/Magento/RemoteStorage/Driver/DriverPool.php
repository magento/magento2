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
class DriverPool extends BaseDriverPool implements DriverPoolInterface
{
    public const PATH_DRIVER = 'remote_storage/driver';
    public const PATH_EXPOSE_URLS = 'remote_storage/expose_urls';
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
     * @var array
     */
    private $pool = [];

    /**
     * @param Config $config
     * @param DriverFactoryPool $driverFactoryPool
     * @param array $extraTypes
     */
    public function __construct(
        Config $config,
        DriverFactoryPool $driverFactoryPool,
        array $extraTypes = []
    ) {
        $this->config = $config;
        $this->driverFactoryPool = $driverFactoryPool;

        parent::__construct($extraTypes);
    }

    /**
     * Retrieves remote driver.
     *
     * @param string $code
     * @return DriverInterface
     * @throws DriverException
     * @throws FileSystemException
     * @throws RuntimeException
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

        return parent::getDriver($code);
    }
}
