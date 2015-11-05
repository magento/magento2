<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Setup\Option\AbstractConfigOption;

class ConfigModel
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsListCollector
     */
    protected $collector;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     */
    protected $writer;

    /**
     * File permissions checker
     *
     * @var FilePermissions
     */
    private $filePermissions;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Constructor
     *
     * @param ConfigOptionsListCollector $collector
     * @param Writer $writer
     * @param DeploymentConfig $deploymentConfig
     * @param FilePermissions $filePermissions
     */
    public function __construct(
        ConfigOptionsListCollector $collector,
        Writer $writer,
        DeploymentConfig $deploymentConfig,
        FilePermissions $filePermissions
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        $this->filePermissions = $filePermissions;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Gets available config options
     *
     * @return AbstractConfigOption[]
     */
    public function getAvailableOptions()
    {
        /** @var AbstractConfigOption[] $optionCollection */
        $optionCollection = [];
        $optionLists = $this->collector->collectOptionsLists();

        foreach ($optionLists as $optionList) {
            $optionCollection = array_merge($optionCollection, $optionList->getOptions());
        }

        foreach ($optionCollection as $option) {
            $currentValue = $this->deploymentConfig->get($option->getConfigPath());
            if ($currentValue !== null) {
                $option->setDefault();
            }
        }

        return $optionCollection;
    }

    /**
     * Process input options
     *
     * @param array $inputOptions
     * @return void
     * @throws \Exception
     */
    public function process($inputOptions)
    {
        $this->checkInstallationFilePermissions();

        $fileConfigStorage = [];
        $options = $this->collector->collectOptionsLists();

        foreach ($options as $moduleName => $option) {

            $configData = $option->createConfig($inputOptions, $this->deploymentConfig);

            foreach ($configData as $config) {
                if (!$config instanceof ConfigData) {
                    throw new \Exception(
                        'In module : '
                        . $moduleName
                        . 'ConfigOption::createConfig should return an array of ConfigData instances'
                    );
                }

                if (isset($fileConfigStorage[$config->getFileKey()])) {
                    $fileConfigStorage[$config->getFileKey()] = array_replace_recursive(
                        $fileConfigStorage[$config->getFileKey()],
                        $config->getData()
                    );
                } else {
                    $fileConfigStorage[$config->getFileKey()] = $config->getData();
                }
            }

        }

        $this->writer->saveConfig($fileConfigStorage, true);
    }

    /**
     * Validates Input Options
     *
     * @param array $inputOptions
     * @return array
     */
    public function validate(array $inputOptions)
    {
        $errors = [];

        //Basic types validation
        $options = $this->getAvailableOptions();
        foreach ($options as $option) {
            try {
                if ($inputOptions[$option->getName()] !== null) {
                    $option->validate($inputOptions[$option->getName()]);
                }
            } catch (\InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }

        // validate ConfigOptionsList
        $options = $this->collector->collectOptionsLists();

        foreach ($options as $option) {
            $errors = array_merge($errors, $option->validate($inputOptions, $this->deploymentConfig));
        }

        return $errors;
    }

    /**
     * Check permissions of directories that are expected to be writable for installation
     *
     * @return void
     * @throws \Exception
     */
    private function checkInstallationFilePermissions()
    {
        $results = $this->filePermissions->getMissingWritableDirectoriesForInstallation();
        if ($results) {
            $errorMsg = "Missing write permissions to the following directories: '" . implode("', '", $results) . "'";
            throw new \Exception($errorMsg);
        }
    }
}
