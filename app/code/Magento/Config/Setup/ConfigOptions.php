<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Setup;

use Magento\Framework\Setup\ConfigOptionsInterface;

/*
 * Deployment configuration options needed for Config Module
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Input key for the options
     */
    const INPUT_KEY_RESOURCE = 'resource';

    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_RESOURCE = 'resource';

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $options)
    {
        $config = [];
        if (isset($options[self::INPUT_KEY_RESOURCE])) {
            $config[self::CONFIG_PATH_RESOURCE] = $options[self::INPUT_KEY_RESOURCE];
        } else {
            $config[self::CONFIG_PATH_RESOURCE] = ['default_setup' => ['connection' => 'default']];
        }
        return $config;
    }
}
