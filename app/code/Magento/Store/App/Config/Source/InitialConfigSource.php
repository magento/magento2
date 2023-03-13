<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Store\Model\Config\Importer;

/**
 * Config source. Retrieve all configuration data from files for specified config type
 */
class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * @param Reader $reader The file reader
     * @param DeploymentConfig $deploymentConfig The deployment config reader
     * @param string $configType The config type
     */
    public function __construct(
        private readonly Reader $reader,
        private readonly DeploymentConfig $deploymentConfig,
        private $configType
    ) {
    }

    /**
     * Return whole config data from config file for specified config type.
     *
     * Ignore $path argument due to config source must return all config data
     *
     * @param string $path
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($path = '')
    {
        /**
         * Magento store configuration should not be read from file source if database is available
         *
         * @see Importer To import store configs
         */
        if ($this->deploymentConfig->isAvailable() || $this->deploymentConfig->isDbAvailable()) {
            return [];
        }

        return $this->reader->load()[$this->configType] ?? [];
    }
}
