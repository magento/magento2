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

/**
 * Deployment configuration consumers options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the option
     */
    const INPUT_KEY_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES ='consumers-wait-for-messages';

    /**
     * Path to the value in the deployment config
     */
    const CONFIG_PATH_QUEUE_CONSUMERS_WAIT_FOR_MESSAGES = 'queue/consumers_wait_for_messages';

    /**
     * Default value
     */
    const DEFAULT_CONSUMERS_WAIT_FOR_MESSAGES = 1;

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
