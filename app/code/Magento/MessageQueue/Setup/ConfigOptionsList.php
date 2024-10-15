<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MessageQueue\Setup;

use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Deployment configuration consumers options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the option
     */
    public const INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES ='consumers-wait-for-messages';
    public const INPUT_KEY_QUEUE_DEFAULT_CONNECTION ='queue-default-connection';

    /**
     * Path to the values in the deployment config
     */
    public const CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES = 'queue/consumers_wait_for_messages';
    public const CONFIG_PATH_QUEUE_DEFAULT_CONNECTION = 'queue/default_connection';

    /**
     * Default value
     */
    public const DEFAULT_CONSUMERS_WAIT_FOR_MESSAGES = 1;
    public const DEFAULT_QUEUE_CONNECTION = 'db';

    /**
     * The available configuration values
     *
     * @var array
     */
    private $selectOptions = [0, 1];

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->selectOptions,
                self::CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES,
                'Should consumers wait for a message from the queue? 1 - Yes, 0 - No',
                self::DEFAULT_CONSUMERS_WAIT_FOR_MESSAGES
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_DEFAULT_CONNECTION,
                'Message queues default connection. Can be \'db\', \'amqp\' or a custom queue system.'
                . 'The queue system must be installed and configured, otherwise messages won\'t be processed correctly.'
            ),
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES)) {
            $configData->set(
                self::CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES,
                (int)$data[self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES]
            );
        }

        if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION)) {
            $configData->set(
                self::CONFIG_PATH_QUEUE_DEFAULT_CONNECTION,
                $data[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION]
            );
        }

        return [$configData];
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES)
            && !in_array($options[self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES], $this->selectOptions)) {
            $errors[] = 'You can use only 1 or 0 for ' . self::INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES . ' option';
        }

        return $errors;
    }

    /**
     * Check if data ($data) with key ($key) is empty
     *
     * @param array $data
     * @param string $key
     * @return bool
     */
    private function isDataEmpty(array $data, $key)
    {
        if (isset($data[$key]) && $data[$key] !== '') {
            return false;
        }

        return true;
    }
}
