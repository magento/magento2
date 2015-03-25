<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\App\DeploymentConfig\Writer;

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
   // private $filePermissions;

    /**
     * Constructor
     *
     * @param ConfigOptionsListCollector $collector
     * @param Writer $writer
     * param FilePermissions $filePermissions
     */
    public function __construct(
        ConfigOptionsListCollector $collector,
        Writer $writer //,
        // FilePermissions $filePermissions
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
        // $this->filePermissions = $filePermissions;
    }

    /**
     * Gets available config options
     *
     * @return array
     */
    public function getAvailableOptions()
    {
        $optionCollection = [];
        $options = $this->collector->collectOptions();

        foreach ($options as $option) {
            $optionCollection = array_merge($optionCollection, $option->getOptions());
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
        $options = $this->collector->collectOptions();

        foreach ($options as $moduleName => $option) {

            $configData = $option->createConfig($inputOptions);

            foreach ($configData as $config) {
                if (!$config instanceof ConfigData) {
                    throw new \Exception(
                        'In module : '
                        . $moduleName
                        . 'ConfigOption::createConfig should return an array of ConfigData instances'
                    );
                }

                if (isset($fileConfigStorage[$config->getFileKey()])
                    && isset($fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()])
                ) {
                    $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()] = array_replace_recursive(
                        $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()],
                        $config->getData()
                    );
                } else {
                    $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()] = $config->getData();
                }
            }

        }

        $this->writer->saveConfig($fileConfigStorage);
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
        $options = $this->collector->collectOptions();

        foreach ($options as $option) {
            $errors = array_merge($errors, $option->validate($inputOptions));
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
        $results = false; // pdltest $this->filePermissions->getMissingWritableDirectoriesForInstallation();
        if ($results) {
            $errorMsg = "Missing writing permissions to the following directories: '" . implode("' '", $results) . "'";
            throw new \Exception($errorMsg);
        }
    }
}
