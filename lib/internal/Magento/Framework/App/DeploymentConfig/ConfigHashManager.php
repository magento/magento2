<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\DeploymentConfig;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig;

/**
 * Manages deployment configuration hash: generates a new hash, checks that hash is valid.
 */
class ConfigHashManager
{
    /**
     * Name of the section where deployment configuration hash is stored.
     */
    const CONFIG_KEY = 'config_hash';

    /**
     * Application deployment configuration.
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Pool of all deployment configuration importers.
     *
     * @var ConfigImporterPool
     */
    private $configImporterPool;

    /**
     * Deployment configuration writer to files.
     *
     * @var Writer
     */
    private $writer;

    /**
     * @param ConfigImporterPool $configImporterPool the pool of all deployment configuration importers
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     * @param Writer $writer the configuration writer that writes to files
     */
    public function __construct(
        ConfigImporterPool $configImporterPool,
        DeploymentConfig $deploymentConfig,
        Writer $writer
    ) {
        $this->configImporterPool = $configImporterPool;
        $this->deploymentConfig = $deploymentConfig;
        $this->writer = $writer;
    }

    /**
     * Checks that deployment configuration hash is valid.
     *
     * Returns true if the hash was not changed or deployment configuration files do not have data.
     *
     * @return bool
     */
    public function isHashValid()
    {
        $config = $this->getConfig();

        if (empty($config)) {
            return true;
        }

        return $this->getHash($config) === $this->getSavedHash();
    }

    /**
     * Retrieves saved hash from storage.
     *
     * @return string|null
     */
    private function getSavedHash()
    {
        return $this->deploymentConfig->getConfigData(self::CONFIG_KEY);
    }

    /**
     * Retrieves data of some sections from deployment configuration files.
     *
     * List of sections are retrieved by ConfigImporterPool class.
     *
     * @return array
     */
    private function getConfig()
    {
        $result = [];

        foreach ($this->configImporterPool->getSections() as $section) {
            $data = $this->deploymentConfig->getConfigData($section);
            if (!empty($data)) {
                $result[$section] = $data;
            }
        }

        return $result;
    }

    /**
     * Generates and saves a new deployment configuration hash to storage.
     *
     * @return void
     */
    public function generateHash()
    {
        $hash = $this->getHash($this->getConfig());
        $this->writer->saveConfig([ConfigFilePool::APP_ENV => [self::CONFIG_KEY => $hash]]);
    }

    /**
     * Generates and retrieves hash of deployment configuration data.
     *
     * @param array|string $data the deployment configuration data from files
     * @return string the hash
     */
    private function getHash($data)
    {
        return sha1(serialize($data));
    }
}
