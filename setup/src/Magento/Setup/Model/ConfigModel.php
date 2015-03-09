<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\App\DeploymentConfig\Writer;

class ConfigModel
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsCollector
     */
    protected $collector;

    /**
     * @var \Magento\Framework\App\DeploymentConfig\Writer
     */
    protected $writer;

    /**
     * Constructor
     *
     * @param ConfigOptionsCollector $collector
     * @param Writer $writer
     */
    public function __construct(
        ConfigOptionsCollector $collector,
        Writer $writer
    ) {
        $this->collector = $collector;
        $this->writer = $writer;
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
     * @throws \Exception
     */
    public function process($inputOptions)
    {
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
                    $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()] = array_merge_recursive(
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
                if ($inputOptions[$option->getName()] !== NULL) {
                    $option->validate($inputOptions[$option->getName()]);
                }
            } catch (\InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }

        // validate ConfigOptions
        $options = $this->collector->collectOptions();

        foreach ($options as $moduleName => $option) {
            $errors = array_merge($errors, $option->validate($inputOptions));
        }

        return $errors;
    }
}
