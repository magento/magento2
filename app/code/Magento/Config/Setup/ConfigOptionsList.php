<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Config\Data\ConfigDataFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\BooleanConfigOption;

/**
 * Deployment configuration options required for the Config module.
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the debug_logging option.
     */
    const INPUT_KEY_DEBUG_LOGGING = 'enable-debug-logging';
    /**
     * Path to the debug_logging value in the deployment config.
     */
    const CONFIG_PATH_DEBUG_LOGGING = 'dev/debug/debug_logging';
    /**
     * Input key for the syslog_logging option.
     */
    const INPUT_KEY_SYSLOG_LOGGING = 'enable-syslog-logging';
    /**
     * Path to the syslog_logging value in the deployment config.
     */
    const CONFIG_PATH_SYSLOG_LOGGING = 'dev/syslog/syslog_logging';

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
            new BooleanConfigOption(
                self::INPUT_KEY_DEBUG_LOGGING,
                self::CONFIG_PATH_DEBUG_LOGGING,
                'Enable debug logging',
                null
            ),
            new BooleanConfigOption(
                self::INPUT_KEY_SYSLOG_LOGGING,
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
            $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);
            $config[] = $configData;
            if (!isset($options[$inputKey])) {
                continue;
            }
            $configData->set(
                $configPath,
                $this->boolVal($options[$inputKey])
            );
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

    /**
     * Convert any valid input option to a boolean
     *
     * @param mixed $option
     *
     * @return int|null
     */
    private function boolVal($option): ?int
    {
        return $option === "" ? null : (int)in_array(strtolower((string)$option), BooleanConfigOption::OPTIONS_POSITIVE);
    }
}
