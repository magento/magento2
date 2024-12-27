<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperLock;
use Magento\Framework\Lock\LockBackendFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Deployment configuration options for locks
 */
class Lock implements ConfigOptionsListInterface
{
    /**
     * The name of an option to set lock provider
     *
     * @const string
     */
    public const INPUT_KEY_LOCK_PROVIDER = 'lock-provider';

    /**
     * The name of an option to set DB prefix
     *
     * @const string
     */
    public const INPUT_KEY_LOCK_DB_PREFIX = 'lock-db-prefix';

    /**
     * The name of an option to set Zookeeper host
     *
     * @const string
     */
    public const INPUT_KEY_LOCK_ZOOKEEPER_HOST = 'lock-zookeeper-host';

    /**
     * The name of an option to set Zookeeper path
     *
     * @const string
     */
    public const INPUT_KEY_LOCK_ZOOKEEPER_PATH = 'lock-zookeeper-path';

    /**
     * The name of an option to set File path
     *
     * @const string
     */
    public const INPUT_KEY_LOCK_FILE_PATH = 'lock-file-path';

    /**
     * The configuration path to save lock provider
     *
     * @const string
     */
    public const CONFIG_PATH_LOCK_PROVIDER = 'lock/provider';

    /**
     * The configuration path to save DB prefix
     *
     * @const string
     */
    public const CONFIG_PATH_LOCK_DB_PREFIX = 'lock/config/prefix';

    /**
     * The configuration path to save Zookeeper host
     *
     * @const string
     */
    public const CONFIG_PATH_LOCK_ZOOKEEPER_HOST = 'lock/config/host';

    /**
     * The configuration path to save Zookeeper path
     *
     * @const string
     */
    public const CONFIG_PATH_LOCK_ZOOKEEPER_PATH = 'lock/config/path';

    /**
     * The configuration path to save locks directory path
     *
     * @const string
     */
    public const CONFIG_PATH_LOCK_FILE_PATH = 'lock/config/path';

    /**
     * The list of lock providers
     *
     * @var array
     */
    private $validLockProviders = [
        LockBackendFactory::LOCK_DB,
        LockBackendFactory::LOCK_ZOOKEEPER,
        LockBackendFactory::LOCK_CACHE,
        LockBackendFactory::LOCK_FILE,
    ];

    /**
     * The mapping input keys with their configuration paths
     *
     * @var array
     */
    private $mappingInputKeyToConfigPath = [
        LockBackendFactory::LOCK_DB => [
            self::INPUT_KEY_LOCK_PROVIDER => self::CONFIG_PATH_LOCK_PROVIDER,
            self::INPUT_KEY_LOCK_DB_PREFIX => self::CONFIG_PATH_LOCK_DB_PREFIX,
        ],
        LockBackendFactory::LOCK_ZOOKEEPER => [
            self::INPUT_KEY_LOCK_PROVIDER => self::CONFIG_PATH_LOCK_PROVIDER,
            self::INPUT_KEY_LOCK_ZOOKEEPER_HOST => self::CONFIG_PATH_LOCK_ZOOKEEPER_HOST,
            self::INPUT_KEY_LOCK_ZOOKEEPER_PATH => self::CONFIG_PATH_LOCK_ZOOKEEPER_PATH,
        ],
        LockBackendFactory::LOCK_CACHE => [
            self::INPUT_KEY_LOCK_PROVIDER => self::CONFIG_PATH_LOCK_PROVIDER,
        ],
        LockBackendFactory::LOCK_FILE => [
            self::INPUT_KEY_LOCK_PROVIDER => self::CONFIG_PATH_LOCK_PROVIDER,
            self::INPUT_KEY_LOCK_FILE_PATH => self::CONFIG_PATH_LOCK_FILE_PATH,
        ],
    ];

    /**
     * The list of default values
     *
     * @var array
     */
    private $defaultConfigValues = [
        self::INPUT_KEY_LOCK_PROVIDER => LockBackendFactory::LOCK_DB,
        self::INPUT_KEY_LOCK_ZOOKEEPER_PATH => ZookeeperLock::DEFAULT_PATH,
    ];

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_LOCK_PROVIDER,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->validLockProviders,
                self::CONFIG_PATH_LOCK_PROVIDER,
                'Lock provider name',
                LockBackendFactory::LOCK_DB
            ),
            new TextConfigOption(
                self::INPUT_KEY_LOCK_DB_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_LOCK_DB_PREFIX,
                'Installation specific lock prefix to avoid lock conflicts'
            ),
            new TextConfigOption(
                self::INPUT_KEY_LOCK_ZOOKEEPER_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_LOCK_ZOOKEEPER_HOST,
                'Host and port to connect to Zookeeper cluster. For example: 127.0.0.1:2181'
            ),
            new TextConfigOption(
                self::INPUT_KEY_LOCK_ZOOKEEPER_PATH,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_LOCK_ZOOKEEPER_PATH,
                'The path where Zookeeper will save locks. The default path is: ' . ZookeeperLock::DEFAULT_PATH
            ),
            new TextConfigOption(
                self::INPUT_KEY_LOCK_FILE_PATH,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_LOCK_FILE_PATH,
                'The path where file locks will be saved.'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        $configData->setOverrideWhenSave(true);
        $lockProvider = $this->getLockProvider($options, $deploymentConfig);

        $this->setDefaultConfiguration($configData, $deploymentConfig, $lockProvider);

        foreach ($this->mappingInputKeyToConfigPath[$lockProvider] as $input => $path) {
            if (isset($options[$input])) {
                $configData->set($path, $options[$input]);
            }
        }

        return $configData;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $lockProvider = $this->getLockProvider($options, $deploymentConfig);
        switch ($lockProvider) {
            case LockBackendFactory::LOCK_ZOOKEEPER:
                $errors = $this->validateZookeeperConfig($options, $deploymentConfig);
                break;
            case LockBackendFactory::LOCK_FILE:
                $errors = $this->validateFileConfig($options, $deploymentConfig);
                break;
            case LockBackendFactory::LOCK_CACHE:
            case LockBackendFactory::LOCK_DB:
                $errors = [];
                break;
            default:
                $errors[] = 'The lock provider ' . $lockProvider . ' does not exist.';
        }

        return $errors;
    }

    /**
     * Validates File locks configuration
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return array
     */
    private function validateFileConfig(array $options, DeploymentConfig $deploymentConfig): array
    {
        $errors = [];

        $path = $options[self::INPUT_KEY_LOCK_FILE_PATH]
            ?? $deploymentConfig->get(
                self::CONFIG_PATH_LOCK_FILE_PATH,
                $this->getDefaultValue(self::INPUT_KEY_LOCK_FILE_PATH)
            );

        if (!$path) {
            $errors[] = 'The path needs to be a non-empty string.';
        }

        return $errors;
    }

    /**
     * Validates Zookeeper configuration
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return array
     */
    private function validateZookeeperConfig(array $options, DeploymentConfig $deploymentConfig): array
    {
        $errors = [];

        if (!extension_loaded(LockBackendFactory::LOCK_ZOOKEEPER)) {
            $errors[] = 'php extension Zookeeper is not installed.';
        }

        $host = $options[self::INPUT_KEY_LOCK_ZOOKEEPER_HOST]
            ?? $deploymentConfig->get(
                self::CONFIG_PATH_LOCK_ZOOKEEPER_HOST,
                $this->getDefaultValue(self::INPUT_KEY_LOCK_ZOOKEEPER_HOST)
            );
        $path = $options[self::INPUT_KEY_LOCK_ZOOKEEPER_PATH]
            ?? $deploymentConfig->get(
                self::CONFIG_PATH_LOCK_ZOOKEEPER_PATH,
                $this->getDefaultValue(self::INPUT_KEY_LOCK_ZOOKEEPER_PATH)
            );

        if (!$path) {
            $errors[] = 'Zookeeper path needs to be a non-empty string.';
        }

        if (!$host) {
            $errors[] = 'Zookeeper host is should be set.';
        }

        return $errors;
    }

    /**
     * Returns the name of lock provider
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return string
     */
    private function getLockProvider(array $options, DeploymentConfig $deploymentConfig): string
    {
        if (!isset($options[self::INPUT_KEY_LOCK_PROVIDER])) {
            return (string) $deploymentConfig->get(
                self::CONFIG_PATH_LOCK_PROVIDER,
                $this->getDefaultValue(self::INPUT_KEY_LOCK_PROVIDER)
            );
        }

        return (string) $options[self::INPUT_KEY_LOCK_PROVIDER];
    }

    /**
     * Sets default configuration for locks
     *
     * @param ConfigData $configData
     * @param DeploymentConfig $deploymentConfig
     * @param string $lockProvider
     * @return ConfigData
     */
    private function setDefaultConfiguration(
        ConfigData $configData,
        DeploymentConfig $deploymentConfig,
        string $lockProvider
    ) {
        foreach ($this->mappingInputKeyToConfigPath[$lockProvider] as $input => $path) {
            // do not set default value null for lock db prefix, but save current value if it exists
            $defaultValue = $deploymentConfig->get($path, $this->getDefaultValue($input));
            if (($input !== self::INPUT_KEY_LOCK_DB_PREFIX) || ($defaultValue !== null)) {
                $configData->set($path, $defaultValue);
            }
        }

        return $configData;
    }

    /**
     * Returns default value by input key
     *
     * If default value is not set returns null
     *
     * @param string $inputKey
     * @return mixed|null
     */
    private function getDefaultValue(string $inputKey)
    {
        return $this->defaultConfigValues[$inputKey] ?? null;
    }
}
