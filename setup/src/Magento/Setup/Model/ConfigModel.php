<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsInterface;
use Magento\Framework\Config\Data\ConfigData;

class ConfigModel
{
    /**
     * @var \Magento\Setup\Model\ConfigOptionsCollector
     */
    protected $collector;

    /**
     * Constructor
     *
     * @param ConfigOptionsCollector $collector
     * @param \Magento\Framework\Config\File\ConfigFilePool $configFilePool
     */
    public function __construct(ConfigOptionsCollector $collector, ConfigFilePool $configFilePool)
    {
        $this->configFilePool = $configFilePool;
        $this->collector = $collector;
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
            // TODO: we need to get rid of keys here
            $optionCollection = array_merge($optionCollection, $option->getOptions());
        }

        return $optionCollection;
    }

    /**
     * Process input options
     *
     * @param array $inputOptions
     */
    public function process($inputOptions)
    {
        // TODO: add error processing and refactor
        $fileConfigStorage = [];

        $options = $this->collector->collectOptions();

        foreach ($options as $moduleName => $option) {

            if (!$option instanceof ConfigOptionsInterface) {
                // TODO: ROMOVE IT!
                echo "FIX IT!: " . 'ConfigOption for module:' . $moduleName . ' does not implement ConfigOptionsInterface' . PHP_EOL;
                continue;
                throw new \Exception(
                    'ConfigOption for module:' . $moduleName . ' does not implement ConfigOptionsInterface'
                );
            }

            $configData = $option->createConfig($inputOptions);
            foreach ($configData as $config) {

                if (!$config instanceof ConfigData) {
                    // TODO: ROMOVE IT!
                    echo "FIX IT!: " . 'In module : ' .$moduleName . 'ConfigOption::createConfig should return instance of ConfigData' . PHP_EOL;
                    continue;
                    throw new \Exception(
                        'In module : ' .$moduleName . 'ConfigOption::createConfig should return instance of ConfigData'
                    );
                }

                if (
                    isset($fileConfigStorage[$config->getFileKey()])
                    && isset($fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()])
                ) {
                    $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()] = array_merge(
                        $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()],
                        $config->getData()
                    );
                } else {
                    $fileConfigStorage[$config->getFileKey()][$config->getSegmentKey()] = $config->getData();
                }
            }

        }

        var_dump($fileConfigStorage);

    }

}
