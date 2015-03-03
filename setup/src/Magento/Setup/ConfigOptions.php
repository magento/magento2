<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup;

use Magento\Framework\Setup\ConfigOptionsInterface;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_INSTALL_DATE = 'install/date';

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        // No user input is required
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data)
    {
        $config = [];
        $config['install']['date'] = date('r');
        return $config;
    }
}
