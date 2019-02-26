<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigDataFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;

/**
 * Deployment configuration options required for the Config module.
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the option.
     */
    const INPUT_KEY_DEBUG_LOGGING = 'enable-debug-logging';

    /**
     * Path to the value in the deployment config.
     */
    const CONFIG_PATH_DEBUG_LOGGING = 'dev/debug/debug_logging';

    /**
     * @var ConfigDataFactory
     */
    private $configDataFactory;

    /**
     * @param ConfigDataFactory $configDataFactory
     */
    public function __construct(ConfigDataFactory $configDataFactory)
    {
        $this->configDataFactory = $configDataFactory;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_DEBUG_LOGGING,
                SelectConfigOption::FRONTEND_WIZARD_RADIO,
                [true, false, 1, 0],
                self::CONFIG_PATH_DEBUG_LOGGING,
                'Enable debug logging'
            )
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $config = [];
        if (isset($options[self::INPUT_KEY_DEBUG_LOGGING])) {
            $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);
            if ($options[self::INPUT_KEY_DEBUG_LOGGING] === 'true'
                || $options[self::INPUT_KEY_DEBUG_LOGGING] === '1') {
                $value = 1;
            } else {
                $value = 0;
            }
            $configData->set(self::CONFIG_PATH_DEBUG_LOGGING, $value);
            $config[] = $configData;
        }

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        return [];
    }
}
