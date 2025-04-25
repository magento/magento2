<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Cache State
 */
class State implements StateInterface, ResetAfterRequestInterface
{
    /**
     * Disallow cache
     */
    public const PARAM_BAN_CACHE = 'global_ban_use_cache';

    /**
     * Deployment config key
     */
    public const CACHE_KEY = 'cache_types';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     *  phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly DeploymentConfig $config;

    /**
     * Deployment configuration storage writer
     *
     * @var Writer
     *
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly Writer $writer;

    /**
     * Associative array of cache type codes and their statuses (enabled/disabled)
     *
     * @var array|null
     */
    private ?array $statuses = null;

    /**
     * Whether all cache types are forced to be disabled
     *
     * @var bool
     * phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
     */
    private readonly bool $banAll;

    /**
     * Constructor
     *
     * @param DeploymentConfig $config
     * @param Writer $writer
     * @param bool $banAll
     */
    public function __construct(DeploymentConfig $config, Writer $writer, $banAll = false)
    {
        $this->config = $config;
        $this->writer = $writer;
        $this->banAll = $banAll;
    }

    /**
     * Whether a cache type is enabled or not at the moment
     *
     * @param string $cacheType
     * @return bool
     */
    public function isEnabled($cacheType): bool
    {
        $this->load();
        return (bool)($this->statuses[$cacheType] ?? false);
    }

    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     */
    public function setEnabled($cacheType, $isEnabled): void
    {
        $this->load();
        $this->statuses[$cacheType] = (int)$isEnabled;
    }

    /**
     * Save the current statuses (enabled/disabled) of cache types to the persistent storage
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function persist(): void
    {
        $this->load();
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => [self::CACHE_KEY => $this->statuses]]);
    }

    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    private function load(): void
    {
        if (null === $this->statuses) {
            $this->statuses = [];
            if ($this->banAll) {
                return;
            }
            $this->statuses = $this->config->getConfigData(self::CACHE_KEY) ?: [];
        }
    }

    /**
     * @inheritdoc
     */
    public function _resetState(): void
    {
        $this->statuses = null;
    }
}
