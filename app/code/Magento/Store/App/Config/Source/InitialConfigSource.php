<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;

/**
 * Config source. Retrieve all configuration data from files for specified config type
 * @since 2.2.0
 */
class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * The file reader
     *
     * @var Reader
     * @since 2.2.0
     */
    private $reader;

    /**
     * The deployment config reader
     *
     * @var DeploymentConfig
     * @since 2.2.0
     */
    private $deploymentConfig;

    /**
     * The config type
     *
     * @var string
     * @since 2.2.0
     */
    private $configType;

    /**
     * @param Reader $reader The file reader
     * @param DeploymentConfig $deploymentConfig The deployment config reader
     * @param string $configType The config type
     * @since 2.2.0
     */
    public function __construct(
        Reader $reader,
        DeploymentConfig $deploymentConfig,
        $configType
    ) {
        $this->reader = $reader;
        $this->deploymentConfig = $deploymentConfig;
        $this->configType = $configType;
    }

    /**
     * Return whole config data from config file for specified config type.
     * Ignore $path argument due to config source must return all config data
     *
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function get($path = '')
    {
        /**
         * Magento store configuration should not be read from file source if database is available
         *
         * @see \Magento\Store\Model\Config\Importer To import store configs
         */
        if ($this->deploymentConfig->isAvailable()) {
            return [];
        }

        return $this->reader->load()[$this->configType] ?? [];
    }
}
