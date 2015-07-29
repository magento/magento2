<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Amqp\Setup;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Validator\DbValidator;
use Magento\Setup\Model\ConfigGenerator;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the options
     */
    const INPUT_KEY_QUEUE_RABBITMQ_HOST = 'rabbitmq-host';
    const INPUT_KEY_QUEUE_RABBITMQ_PORT = 'rabbitmq-port';
    const INPUT_KEY_QUEUE_RABBITMQ_USER = 'rabbitmq-user';
    const INPUT_KEY_QUEUE_RABBITMQ_PASSWORD = 'rabbitmq-password';
    const INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST = 'rabbitmq-virtualhost';
    const INPUT_KEY_QUEUE_RABBITMQ_SSL = 'rabbitmq-ssl';

    /**
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_QUEUE_RABBITMQ_HOST = 'queue/rabbit/host';
    const CONFIG_PATH_QUEUE_RABBITMQ_PORT = 'queue/rabbit/port';
    const CONFIG_PATH_QUEUE_RABBITMQ_USER = 'queue/rabbit/user';
    const CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD = 'queue/rabbit/password';
    const CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST = 'queue/rabbit/virtualhost';
    const CONFIG_PATH_QUEUE_RABBITMQ_SSL = 'queue/rabbit/ssl';

    /**
     * Generate config data for individual segments
     *
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * Constructor
     *
     * @param ConfigGenerator $configGenerator
     */
    public function __construct(ConfigGenerator $configGenerator)
    {
        $this->configGenerator = $configGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_HOST,
                'RabbitMQ server host',
                'localhost'
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_PORT,
                'RabbitMQ server port',
                '5672'
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_USER,
                'RabbitMQ server username',
                'root'
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD,
                'RabbitMQ server password',
                ''
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST,
                'RabbitMQ virtualhost',
                ''
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_RABBITMQ_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_RABBITMQ_SSL,
                'RabbitMQ SSL',
                ''
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_HOST])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_HOST, $data[self::INPUT_KEY_QUEUE_RABBITMQ_HOST]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_PORT])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_PORT, $data[self::INPUT_KEY_QUEUE_RABBITMQ_PORT]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_USER])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_USER, $data[self::INPUT_KEY_QUEUE_RABBITMQ_USER]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD])) {
            $configData->set(self::CONFIG_PATH_QUEUE_RABBITMQ_PASSWORD, $data[self::INPUT_KEY_QUEUE_RABBITMQ_PASSWORD]);
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST])) {
            $configData->set(
                self::CONFIG_PATH_QUEUE_RABBITMQ_VIRTUAL_HOST,
                $data[self::INPUT_KEY_QUEUE_RABBITMQ_VIRTUAL_HOST]
            );
        }

        if (isset($data[self::INPUT_KEY_QUEUE_RABBITMQ_SSL])) {
            $configData->set(
                self::CONFIG_PATH_QUEUE_RABBITMQ_SSL,
                $data[self::INPUT_KEY_QUEUE_RABBITMQ_SSL]
            );
        }

        return [$configData];
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        /* TODO: should validate that the options are set correctly like the database validator */

        return $errors;
    }

    /**
     * Returns other parts of existing db config in case is only one value is presented by user
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     *
     * @return array
     */
    private function getDbSettings(array $options, DeploymentConfig $deploymentConfig)
    {
        if ($options[ConfigOptionsListConstants::INPUT_KEY_DB_NAME] === null) {
            $options[ConfigOptionsListConstants::INPUT_KEY_DB_NAME] =
                $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_NAME
                );
        }

        if ($options[ConfigOptionsListConstants::INPUT_KEY_DB_HOST] === null) {
            $options[ConfigOptionsListConstants::INPUT_KEY_DB_HOST] =
                $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_HOST
                );
        }

        if ($options[ConfigOptionsListConstants::INPUT_KEY_DB_USER] === null) {
            $options[ConfigOptionsListConstants::INPUT_KEY_DB_USER] =
                $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_USER
                );
        }

        if ($options[ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD] === null) {
            $options[ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD] =
                $deploymentConfig->get(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                    '/' . ConfigOptionsListConstants::KEY_PASSWORD
                );
        }

        return $options;
    }

    /**
     * Validates session save param
     *
     * @param array $options
     * @return string[]
     */
    private function validateSessionSave(array $options)
    {
        $errors = [];

        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE])) {
            if ($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]
                != ConfigOptionsListConstants::SESSION_SAVE_FILES
                && $options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]
                != ConfigOptionsListConstants::SESSION_SAVE_DB
            ) {
                $errors[] = 'Invalid session save location.';
            }
        }

        return $errors;
    }

    /**
     * Validates encryption key param
     *
     * @param array $options
     * @return string[]
     */
    private function validateEncryptionKey(array $options)
    {
        $errors = [];

        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY])
            && !$options[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY]) {
            $errors[] = 'Invalid encryption key.';
        }

        return $errors;
    }
}
