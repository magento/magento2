<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Setup\Option\AbstractConfigOption;
use Magento\Framework\Setup\FilePermissions;

/**
 * Class \Magento\Setup\Model\ConfigModel
 *
 * @since 2.0.0
 */
class ConfigModel
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsListCollector
     * @since 2.0.0
     */
    protected $collector;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     * @since 2.0.0
     */
    protected $writer;

    /**
     * File permissions checker
     *
     * @var FilePermissions
     * @since 2.0.0
     */
    private $filePermissions;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     * @since 2.0.0
     */
    protected $deploymentConfig;

    /**
     * Constructor
     *
     * @param ConfigOptionsListCollector $collector
     * @param Writer $writer
     * @param DeploymentConfig $deploymentConfig
     * @param FilePermissions $filePermissions
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function process($inputOptions)
    {
        $this->checkInstallationFilePermissions();

        $options = $this->collector->collectOptionsLists();

        foreach ($options as $moduleName => $option) {
            $configData = $option->createConfig($inputOptions, $this->deploymentConfig);

            foreach ($configData as $config) {
                $fileConfigStorage = [];
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
                $this->writer->saveConfig($fileConfigStorage, $config->isOverrideWhenSave());
            }
        }
    }

    /**
     * Validates Input Options
     *
     * @param array $inputOptions
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
     */
    private function checkInstallationFilePermissions()
    {
        $results = $this->filePermissions->getMissingWritablePathsForInstallation();
        if ($results) {
            $errorMsg = "Missing write permissions to the following paths:" . PHP_EOL . implode(PHP_EOL, $results);
            throw new \Exception($errorMsg);
        }
    }
}
