<?php
/**
 * An ultimate accessor to cache types' statuses
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;

/**
 * Class \Magento\Framework\App\Cache\State
 *
 * @since 2.0.0
 */
class State implements StateInterface
{
    /**
     * Disallow cache
     */
    const PARAM_BAN_CACHE = 'global_ban_use_cache';

    /**
     * Deployment config key
     */
    const CACHE_KEY = 'cache_types';

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     * @since 2.0.0
     */
    private $config;

    /**
     * Deployment configuration storage writer
     *
     * @var Writer
     * @since 2.0.0
     */
    private $writer;

    /**
     * Associative array of cache type codes and their statuses (enabled/disabled)
     *
     * @var array
     * @since 2.0.0
     */
    private $statuses;

    /**
     * Whether all cache types are forced to be disabled
     *
     * @var bool
     * @since 2.0.0
     */
    private $banAll;

    /**
     * Constructor
     *
     * @param DeploymentConfig $config
     * @param Writer $writer
     * @param bool $banAll
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function isEnabled($cacheType)
    {
        $this->load();
        return isset($this->statuses[$cacheType]) ? (bool)$this->statuses[$cacheType] : false;
    }

    /**
     * Enable/disable a cache type in run-time
     *
     * @param string $cacheType
     * @param bool $isEnabled
     * @return void
     * @since 2.0.0
     */
    public function setEnabled($cacheType, $isEnabled)
    {
        $this->load();
        $this->statuses[$cacheType] = (int)$isEnabled;
    }

    /**
     * Save the current statuses (enabled/disabled) of cache types to the persistent storage
     *
     * @return void
     * @since 2.0.0
     */
    public function persist()
    {
        $this->load();
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => [self::CACHE_KEY => $this->statuses]]);
    }

    /**
     * Load statuses (enabled/disabled) of cache types
     *
     * @return void
     * @since 2.0.0
     */
    private function load()
    {
        if (null === $this->statuses) {
            $this->statuses = [];
            if ($this->banAll) {
                return;
            }
            $this->statuses = $this->config->getConfigData(self::CACHE_KEY) ?: [];
        }
    }
}
