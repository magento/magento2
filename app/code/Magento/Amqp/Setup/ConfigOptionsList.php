<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Amqp\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\MessageQueue\Setup\ConfigOptionsList as MessageQueueConfigOptionsList;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the options
     */
    public const INPUT_KEY_QUEUE_AMQP_HOST = 'amqp-host';
    public const INPUT_KEY_QUEUE_AMQP_PORT = 'amqp-port';
    public const INPUT_KEY_QUEUE_AMQP_USER = 'amqp-user';
    public const INPUT_KEY_QUEUE_AMQP_PASSWORD = 'amqp-password';
    public const INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST = 'amqp-virtualhost';
    public const INPUT_KEY_QUEUE_AMQP_SSL = 'amqp-ssl';
    public const INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS = 'amqp-ssl-options';

    /**
     * Path to the values in the deployment config
     */
    public const CONFIG_PATH_QUEUE_AMQP_HOST = 'queue/amqp/host';
    public const CONFIG_PATH_QUEUE_AMQP_PORT = 'queue/amqp/port';
    public const CONFIG_PATH_QUEUE_AMQP_USER = 'queue/amqp/user';
    public const CONFIG_PATH_QUEUE_AMQP_PASSWORD = 'queue/amqp/password';
    public const CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST = 'queue/amqp/virtualhost';
    public const CONFIG_PATH_QUEUE_AMQP_SSL = 'queue/amqp/ssl';
    public const CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS = 'queue/amqp/ssl_options';

    /**
     * Default values
     */
    public const DEFAULT_AMQP_HOST = '';
    public const DEFAULT_AMQP_PORT = '5672';
    public const DEFAULT_AMQP_USER = '';
    public const DEFAULT_AMQP_PASSWORD = '';
    public const DEFAULT_AMQP_VIRTUAL_HOST = '/';
    public const DEFAULT_AMQP_SSL = '';

    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

    /**
     * @param ConnectionValidator $connectionValidator
     */
    public function __construct(ConnectionValidator $connectionValidator)
    {
        $this->connectionValidator = $connectionValidator;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_HOST,
                'Amqp server host',
                self::DEFAULT_AMQP_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_PORT,
                'Amqp server port',
                self::DEFAULT_AMQP_PORT
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_USER,
                'Amqp server username',
                self::DEFAULT_AMQP_USER
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_PASSWORD,
                'Amqp server password',
                self::DEFAULT_AMQP_PASSWORD
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST,
                'Amqp virtualhost',
                self::DEFAULT_AMQP_VIRTUAL_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_AMQP_SSL,
                'Amqp SSL',
                self::DEFAULT_AMQP_SSL
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS,
                TextConfigOption::FRONTEND_WIZARD_TEXTAREA,
                self::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS,
                'Amqp SSL Options (JSON)',
                self::DEFAULT_AMQP_SSL
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

        if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_HOST)) {
            $configData->set(self::CONFIG_PATH_QUEUE_AMQP_HOST, $data[self::INPUT_KEY_QUEUE_AMQP_HOST]);
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_PORT)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_PORT, $data[self::INPUT_KEY_QUEUE_AMQP_PORT]);
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_USER)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_USER, $data[self::INPUT_KEY_QUEUE_AMQP_USER]);
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_PASSWORD)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_PASSWORD, $data[self::INPUT_KEY_QUEUE_AMQP_PASSWORD]);
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST)) {
                $configData->set(
                    self::CONFIG_PATH_QUEUE_AMQP_VIRTUAL_HOST,
                    $data[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST]
                );
            }
            if (!$this->isDataEmpty($data, self::INPUT_KEY_QUEUE_AMQP_SSL)) {
                $configData->set(self::CONFIG_PATH_QUEUE_AMQP_SSL, $data[self::INPUT_KEY_QUEUE_AMQP_SSL]);
            }
            if (!$this->isDataEmpty(
                $data,
                self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS
            )) {
                $options = json_decode(
                    $data[self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS],
                    true
                );
                if ($options !== null) {
                    $configData->set(
                        self::CONFIG_PATH_QUEUE_AMQP_SSL_OPTIONS,
                        $options
                    );
                }
            }
        }

        return [$configData];
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $defaultConnection = $options[MessageQueueConfigOptionsList::INPUT_KEY_QUEUE_DEFAULT_CONNECTION] ?? null;
        if ($defaultConnection && $defaultConnection !== 'amqp') {
            return [];
        }

        if (empty($options[self::INPUT_KEY_QUEUE_AMQP_HOST])) {
            return [];
        }

        return $this->validateAmqpConnection($options);
    }

    /**
     * Validate AMQP connection and RabbitMQ version.
     *
     * @param array $options
     * @return array
     */
    private function validateAmqpConnection(array $options): array
    {
        $errors = [];
        $sslOptions = $this->parseSslOptions($options);
        $isSslEnabled = $this->isSslEnabled($options);

        $result = $this->connectionValidator->isConnectionValid(
            $options[self::INPUT_KEY_QUEUE_AMQP_HOST],
            $options[self::INPUT_KEY_QUEUE_AMQP_PORT],
            $options[self::INPUT_KEY_QUEUE_AMQP_USER],
            $options[self::INPUT_KEY_QUEUE_AMQP_PASSWORD],
            $options[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST],
            $isSslEnabled,
            $sslOptions
        );

        if (!$result) {
            $errors[] = "Could not connect to the Amqp Server.";
        }

        // Validate RabbitMQ version if connection succeeded
        if ($result) {
            $versionError = $this->validateVersion($options, $isSslEnabled, $sslOptions);
            if ($versionError !== null) {
                $errors[] = $versionError;
            }
        }

        return $errors;
    }

    /**
     * Parse SSL options from config options.
     *
     * @param array $options
     * @return array|null
     */
    private function parseSslOptions(array $options): ?array
    {
        if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS)) {
            return json_decode($options[self::INPUT_KEY_QUEUE_AMQP_SSL_OPTIONS], true);
        }
        return null;
    }

    /**
     * Check if SSL is enabled.
     *
     * @param array $options
     * @return bool
     */
    private function isSslEnabled(array $options): bool
    {
        return !empty($options[self::INPUT_KEY_QUEUE_AMQP_SSL])
            && $options[self::INPUT_KEY_QUEUE_AMQP_SSL] !== 'false';
    }

    /**
     * Validate RabbitMQ version.
     *
     * @param array $options
     * @param bool $isSslEnabled
     * @param array|null $sslOptions
     * @return string|null Error message or null
     */
    private function validateVersion(array $options, bool $isSslEnabled, ?array $sslOptions): ?string
    {
        $serverVersion = $this->connectionValidator->getServerVersion(
            $options[self::INPUT_KEY_QUEUE_AMQP_HOST],
            $options[self::INPUT_KEY_QUEUE_AMQP_PORT],
            $options[self::INPUT_KEY_QUEUE_AMQP_USER],
            $options[self::INPUT_KEY_QUEUE_AMQP_PASSWORD],
            $options[self::INPUT_KEY_QUEUE_AMQP_VIRTUAL_HOST],
            $isSslEnabled,
            $sslOptions
        );

        if ($serverVersion !== null
            && version_compare($serverVersion, ConnectionValidator::MINIMUM_RABBITMQ_VERSION, '<')
        ) {
            return sprintf(
                'RabbitMQ version "%s" detected. Magento requires RabbitMQ version %s or later. '
                . 'Please upgrade RabbitMQ and rerun setup.',
                $serverVersion,
                ConnectionValidator::MINIMUM_RABBITMQ_VERSION
            );
        }

        return null;
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
