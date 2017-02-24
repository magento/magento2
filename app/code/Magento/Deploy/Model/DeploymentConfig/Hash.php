<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Model\DeploymentConfig;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\FileSystemException;

/**
 * Saves and Retrieves deployment configuration hash.
 */
class Hash
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
     * Deployment configuration writer to files.
     *
     * @var Writer
     */
    private $writer;

    /**
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     * @param Writer $writer the configuration writer that writes to files
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        Writer $writer
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->writer = $writer;
    }

    /**
     * Saves a deployment configuration hash to storage.
     *
     * @param array|string $hash the deployment configuration data from files
     * @return void
     * @throws LocalizedException
     */
    public function save($hash)
    {
        try {
            $this->writer->saveConfig([ConfigFilePool::APP_ENV => [self::CONFIG_KEY => $hash]]);
        } catch (FileSystemException $exception) {
            throw new LocalizedException(__('Hash has not been saved'), $exception);
        }
    }

    /**
     * Retrieves saved hash from storage.
     *
     * @return string|null
     */
    public function get()
    {
        return $this->deploymentConfig->getConfigData(self::CONFIG_KEY);
    }
}
