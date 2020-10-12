<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\Framework\Filesystem\DriverPool as BaseDriverPool;

/**
 * Configuration for remote storage.
 */
class Config
{
    /**
     * @var DeploymentConfig
     */
    private $config;

    /**
     * @param DeploymentConfig $config
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve driver name.
     *
     * @return string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getDriver(): ?string
    {
        return $this->config->get(DriverPool::PATH_DRIVER, null);
    }

    /**
     * Check if remote FS is enabled.
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isEnabled(): bool
    {
        $driver = $this->config->get(DriverPool::PATH_DRIVER);

        return $driver && $driver !== BaseDriverPool::FILE;
    }

    /**
     * Use remote URL for public URLs.
     *
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function isPublic(): bool
    {
        return (bool)$this->config->get(DriverPool::PATH_IS_PUBLIC, false);
    }

    /**
     * Retrieves config.
     *
     * @return array
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getConfig(): array
    {
        return (array)$this->config->get(DriverPool::PATH_CONFIG, []);
    }

    /**
     * Retrieves prefix.
     *
     * @return string
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getPrefix(): string
    {
        return (string)$this->config->get(DriverPool::PATH_PREFIX, '');
    }
}
