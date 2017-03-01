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
     * Hash generator.
     *
     * @var Hash\Generator
     */
    private $configHashGenerator;

    /**
     * Config data collector.
     *
     * @var DataCollector
     */
    private $dataConfigCollector;

    /**
     * @param DeploymentConfig $deploymentConfig the application deployment configuration
     * @param Writer $writer the configuration writer that writes to files
     * @param Hash\Generator $configHashGenerator the hash generator
     * @param DataCollector $dataConfigCollector the config data collector
     */
    public function __construct(
        DeploymentConfig $deploymentConfig,
        Writer $writer,
        Hash\Generator $configHashGenerator,
        DataCollector $dataConfigCollector
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->writer = $writer;
        $this->configHashGenerator = $configHashGenerator;
        $this->dataConfigCollector = $dataConfigCollector;
    }

    /**
     * Updates hash in the storage.
     *
     * If the specific section name is set, then hash will be updated only for this section,
     * in another case hash will be updated for all sections which defined in di.xml
     *
     * @param string $sectionName the specific section name
     * @return void
     * @throws LocalizedException is thrown when hash is not saved in a storage
     */
    public function regenerate($sectionName = null)
    {
        try {
            $hashes = $this->get();
            $configs = $this->dataConfigCollector->getConfig($sectionName ?: null);

            foreach ($configs as $section => $config) {
                $hashes[$section] = $this->configHashGenerator->generate($config);
            }

            $this->writer->saveConfig([ConfigFilePool::APP_ENV => [self::CONFIG_KEY => $hashes]]);
        } catch (FileSystemException $exception) {
            throw new LocalizedException(__('Hash has not been saved.'), $exception);
        }
    }

    /**
     * Retrieves saved hashes from storage.
     *
     * @return array
     */
    public function get()
    {
        return (array) ($this->deploymentConfig->getConfigData(self::CONFIG_KEY) ?: []);
    }
}
