<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\File\ConfigFilePool;

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
            if ($option['enabled']) {
                $optionCollection = array_merge($optionCollection, $option['options']);
            }
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
        $fileConfigStorage = [];

        $options = $this->collector->collectOptions();
        foreach ($options as $option) {
            if ($option['enabled']) {
                // TODO: add isset and check of instance here
                $conf = $option['configOption']->createConfig($inputOptions);

                // TODO: this file should be returned by ConfigOption
                $defaultConfigFile = ConfigFilePool::APP_CONFIG;

                if (isset($fileConfigStorage[$defaultConfigFile])) {
                    $fileConfigStorage[$defaultConfigFile] = array_merge(
                        $fileConfigStorage[$defaultConfigFile],
                        $conf
                        );
                } else {
                    $fileConfigStorage[$defaultConfigFile] = $conf;
                }
            }
        }

        var_dump($fileConfigStorage);

    }

}
