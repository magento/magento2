<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Stomp\Setup;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Input key for the options
     */
    public const INPUT_KEY_QUEUE_STOMP_HOST = 'stomp-host';
    public const INPUT_KEY_QUEUE_STOMP_PORT = 'stomp-port';
    public const INPUT_KEY_QUEUE_STOMP_USER = 'stomp-user';
    public const INPUT_KEY_QUEUE_STOMP_PASSWORD = 'stomp-password';
    public const INPUT_KEY_QUEUE_STOMP_SSL = 'stomp-ssl';
    public const INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS = 'stomp-ssl-options';
    public const INPUT_KEY_QUEUE_DEFAULT_CONNECTION ='queue-default-connection';

    /**
     * Path to the values in the deployment config
     */
    public const CONFIG_PATH_QUEUE_STOMP_HOST = 'queue/stomp/host';
    public const CONFIG_PATH_QUEUE_STOMP_PORT = 'queue/stomp/port';
    public const CONFIG_PATH_QUEUE_STOMP_USER = 'queue/stomp/user';
    public const CONFIG_PATH_QUEUE_STOMP_PASSWORD = 'queue/stomp/password';
    public const CONFIG_PATH_QUEUE_STOMP_SSL = 'queue/stomp/ssl';
    public const CONFIG_PATH_QUEUE_STOMP_SSL_OPTIONS = 'queue/stomp/ssl_options';

    /**
     * Default values
     */
    public const DEFAULT_STOMP_HOST = '';
    public const DEFAULT_STOMP_PORT = '61613';
    public const DEFAULT_STOMP_USER = '';
    public const DEFAULT_STOMP_PASSWORD = '';
    public const DEFAULT_STOMP_SSL = '';

    /**
     * @var ConnectionValidator
     */
    private $connectionValidator;

    /**
     * Constructor
     *
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
                self::INPUT_KEY_QUEUE_STOMP_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_STOMP_HOST,
                'Stomp server host',
                self::DEFAULT_STOMP_HOST
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_STOMP_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_STOMP_PORT,
                'Stomp server port',
                self::DEFAULT_STOMP_PORT
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_STOMP_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_STOMP_USER,
                'Stomp server username',
                self::DEFAULT_STOMP_USER
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_STOMP_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_STOMP_PASSWORD,
                'Stomp server password',
                self::DEFAULT_STOMP_PASSWORD
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_STOMP_SSL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_QUEUE_STOMP_SSL,
                'Stomp SSL',
                self::DEFAULT_STOMP_SSL
            ),
            new TextConfigOption(
                self::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS,
                TextConfigOption::FRONTEND_WIZARD_TEXTAREA,
                self::CONFIG_PATH_QUEUE_STOMP_SSL_OPTIONS,
                'Stomp SSL Options (JSON)',
                self::DEFAULT_STOMP_SSL
            ),
        ];
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_STOMP_HOST)) {
            $configData->set(self::CONFIG_PATH_QUEUE_STOMP_HOST, $options[self::INPUT_KEY_QUEUE_STOMP_HOST]);
            if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_STOMP_PORT)) {
                $configData->set(self::CONFIG_PATH_QUEUE_STOMP_PORT, $options[self::INPUT_KEY_QUEUE_STOMP_PORT]);
            }
            if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_STOMP_USER)) {
                $configData->set(self::CONFIG_PATH_QUEUE_STOMP_USER, $options[self::INPUT_KEY_QUEUE_STOMP_USER]);
            }
            if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_STOMP_PASSWORD)) {
                $configData->set(
                    self::CONFIG_PATH_QUEUE_STOMP_PASSWORD,
                    $options[self::INPUT_KEY_QUEUE_STOMP_PASSWORD]
                );
            }
            if (!$this->isDataEmpty($options, self::INPUT_KEY_QUEUE_STOMP_SSL)) {
                $configData->set(self::CONFIG_PATH_QUEUE_STOMP_SSL, $options[self::INPUT_KEY_QUEUE_STOMP_SSL]);
            }
            if (!$this->isDataEmpty(
                $options,
                self::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS
            )) {
                $optionsArray = json_decode(
                    $options[self::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS],
                    true
                );
                if ($optionsArray !== null) {
                    $configData->set(
                        self::CONFIG_PATH_QUEUE_STOMP_SSL_OPTIONS,
                        $optionsArray
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
        $errors = [];

        if (isset($options[self::INPUT_KEY_QUEUE_STOMP_HOST])
            && $options[self::INPUT_KEY_QUEUE_STOMP_HOST] !== '') {
            if (!$this->isDataEmpty(
                $options,
                self::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS
            )) {
                $sslOptions = json_decode(
                    $options[self::INPUT_KEY_QUEUE_STOMP_SSL_OPTIONS],
                    true
                );
            } else {
                $sslOptions = null;
            }
            $isSslEnabled = !empty($options[self::INPUT_KEY_QUEUE_STOMP_SSL])
                && $options[self::INPUT_KEY_QUEUE_STOMP_SSL] !== 'false';

            $result = $this->connectionValidator->isConnectionValid(
                $options[self::INPUT_KEY_QUEUE_STOMP_HOST],
                $options[self::INPUT_KEY_QUEUE_STOMP_PORT],
                $options[self::INPUT_KEY_QUEUE_STOMP_USER],
                $options[self::INPUT_KEY_QUEUE_STOMP_PASSWORD],
                $isSslEnabled,
                $sslOptions
            );

            if (!$result) {
                $errors[] = "Could not connect to the Stomp Server.";
            }

            if (isset($options[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION])
                && $options[self::INPUT_KEY_QUEUE_DEFAULT_CONNECTION] === 'amqp') {
                $errors = [];
            }
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
    private function isDataEmpty(array $data, string $key): bool
    {
        if (isset($data[$key]) && $data[$key] !== '') {
            return false;
        }

        return true;
    }
}
