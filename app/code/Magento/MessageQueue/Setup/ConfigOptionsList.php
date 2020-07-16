<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\BooleanConfigOption;

/**
 * Deployment configuration consumers options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the option
     */
    const INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES = 'consumers-wait-for-messages';
    /**
     * Path to the value in the deployment config
     */
    const CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES = 'queue/consumers_wait_for_messages';
    /**
     * Default value
     */
    const DEFAULT_CONSUMERS_WAIT_FOR_MESSAGES = 1;

    /**
     * @inheritdoc
     */
    public function getOptions(): array
    {
        return [
            new BooleanConfigOption(
                self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES,
                self::CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES,
                'Should consumers wait for a message from the queue?',
                self::DEFAULT_CONSUMERS_WAIT_FOR_MESSAGES
            ),
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig): ConfigData
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (!isset($data[self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES])
            || $data[self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES] === ''
        ) {
            return $configData;
        }

        $configData->set(
            self::CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES,
            $this->boolVal($data[self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES])
        );

        return $configData;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig): array
    {
        return [];
    }

    /**
     * Convert any valid input option to a boolean
     *
     * @param mixed $option
     *
     * @return bool
     */
    private function boolVal($option): bool
    {
        return in_array(strtolower((string)$option), BooleanConfigOption::OPTIONS_POSITIVE);
    }
}
