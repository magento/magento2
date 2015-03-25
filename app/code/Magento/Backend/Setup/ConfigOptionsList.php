<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Setup;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;

/*
 * Deployment configuration options needed for Backend module
 */
class ConfigOptionsList implements ConfigOptionsListInterface
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
     * Key for config
     */
    const KEY_FRONTNAME = 'frontend';

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_BACKEND_FRONTNAME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'backend/frontName',
                'Backend frontname',
                'admin'
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $options, array $currentConfig = [])
    {
        $data = [];
        if (isset($options[self::INPUT_KEY_BACKEND_FRONTNAME])) {
            $data = ['frontName' => $options[self::INPUT_KEY_BACKEND_FRONTNAME]];
        }
        return [new ConfigData(
            ConfigFilePool::APP_CONFIG,
            'backend',
            $data
        )];
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options)
    {
        $errors = [];
        if (isset($options[self::INPUT_KEY_BACKEND_FRONTNAME])
            && !preg_match('/^[a-zA-Z0-9_]+$/', $options[self::INPUT_KEY_BACKEND_FRONTNAME])
        ) {
            $errors[] = "Invalid backend frontname '{$options[self::INPUT_KEY_BACKEND_FRONTNAME]}'";
        }

        return $errors;
    }
}
