<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Setup;

use Magento\Framework\Setup\ConfigOption;
use Magento\Framework\Setup\ConfigOptionsInterface;
use Symfony\Component\Console\Input\InputOption;

/*
 * Deployment configuration options needed for Backend module
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**
     * Input key for the options
     */
    const INPUT_KEY_BACKEND_FRONTNAME = 'backend_frontname';

    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_BACKEND_FRONTNAME = 'backend/frontName';

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new ConfigOption(
                self::INPUT_KEY_BACKEND_FRONTNAME,
                ConfigOption::FRONTEND_WIZARD_TEXT,
                'Backenad frontname (e.g., "backend" or "admin")',
                [],
                '',
                InputOption::VALUE_REQUIRED
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $options)
    {
        $config = [];
        if (empty($options[self::INPUT_KEY_BACKEND_FRONTNAME])) {
            throw new \InvalidArgumentException('No backend frontname provided.');
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $options[self::INPUT_KEY_BACKEND_FRONTNAME])) {
            throw new \InvalidArgumentException(
                "Invalid backend frontname {$options[self::INPUT_KEY_BACKEND_FRONTNAME]}"
            );
        }
        $config['backend']['frontName'] = $options[self::INPUT_KEY_BACKEND_FRONTNAME];
        return $config;
    }
}
