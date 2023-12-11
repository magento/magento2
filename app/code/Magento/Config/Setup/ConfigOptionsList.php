<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
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
     * Input key for the debug_logging option.
     */
    public const INPUT_KEY_DEBUG_LOGGING = 'enable-debug-logging';

    /**
     * Path to the debug_logging value in the deployment config.
     */
    public const CONFIG_PATH_DEBUG_LOGGING = 'dev/debug/debug_logging';

    /**
     * Input key for the syslog_logging option.
     */
    public const INPUT_KEY_SYSLOG_LOGGING = 'enable-syslog-logging';

    /**
     * Path to the syslog_logging value in the deployment config.
     */
    public const CONFIG_PATH_SYSLOG_LOGGING = 'dev/syslog/syslog_logging';

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
                ['true', 'false', 1, 0],
                self::CONFIG_PATH_DEBUG_LOGGING,
                'Enable debug logging'
            ),
            new SelectConfigOption(
                self::INPUT_KEY_SYSLOG_LOGGING,
                SelectConfigOption::FRONTEND_WIZARD_RADIO,
                ['true', 'false', 1, 0],
                self::CONFIG_PATH_SYSLOG_LOGGING,
                'Enable syslog logging'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $deploymentOption = [
            self::INPUT_KEY_DEBUG_LOGGING => self::CONFIG_PATH_DEBUG_LOGGING,
            self::INPUT_KEY_SYSLOG_LOGGING => self::CONFIG_PATH_SYSLOG_LOGGING,
        ];

        $config = [];
        foreach ($deploymentOption as $inputKey => $configPath) {
            $configValue = $this->processBooleanConfigValue(
                $inputKey,
                $configPath,
                $options
            );
            if ($configValue) {
                $config[] = $configValue;
            }
        }

        return $config;
    }

    /**
     * Provide config value from input.
     *
     * @param string $inputKey
     * @param string $configPath
     * @param array $options
     * @return ConfigData|null
     */
    private function processBooleanConfigValue(string $inputKey, string $configPath, array &$options): ?ConfigData
    {
        $configData = null;
        if (isset($options[$inputKey])) {
            $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);
            if ($options[$inputKey] === 'true'
                || $options[$inputKey] === '1') {
                $value = 1;
            } else {
                $value = 0;
            }
            $configData->set($configPath, $value);
        }

        return $configData;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        return [];
    }
}
