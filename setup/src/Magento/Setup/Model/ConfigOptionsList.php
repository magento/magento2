<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Encryption\KeyValidator;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\FlagConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Model\ConfigOptionsList\DriverOptions;
use Magento\Setup\Validator\DbValidator;

/**
 * Deployment configuration options needed for Setup application
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Generate config data for individual segments
     *
     * @var  ConfigGenerator
     */
    private $configGenerator;

    /**
     * @var \Magento\Setup\Validator\DbValidator
     */
    private $dbValidator;

    /**
     * @var array
     */
    private $configOptionsCollection = [];

    /**
     * @var KeyValidator
     */
    private $encryptionKeyValidator;

    /**
     * @var array
     */
    private $configOptionsListClasses = [
        \Magento\Setup\Model\ConfigOptionsList\Session::class,
        \Magento\Setup\Model\ConfigOptionsList\Cache::class,
        \Magento\Setup\Model\ConfigOptionsList\PageCache::class,
        \Magento\Setup\Model\ConfigOptionsList\Lock::class,
        \Magento\Setup\Model\ConfigOptionsList\Directory::class,
    ];

    /**
     * @var DriverOptions
     */
    private $driverOptions;

    /**
     * Constructor
     *
     * @param ConfigGenerator $configGenerator
     * @param DbValidator $dbValidator
     * @param KeyValidator|null $encryptionKeyValidator
     * @param DriverOptions|null $driverOptions
     */
    public function __construct(
        ConfigGenerator $configGenerator,
        DbValidator $dbValidator,
        KeyValidator $encryptionKeyValidator = null,
        DriverOptions $driverOptions = null
    ) {
        $this->configGenerator = $configGenerator;
        $this->dbValidator = $dbValidator;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        foreach ($this->configOptionsListClasses as $className) {
            $this->configOptionsCollection[] = $objectManager->get($className);
        }
        $this->encryptionKeyValidator = $encryptionKeyValidator ?: $objectManager->get(KeyValidator::class);
        $this->driverOptions = $driverOptions ?? $objectManager->get(DriverOptions::class);
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptions()
    {
        $options = [
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY,
                'Encryption key'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_HOST,
                'Database server host',
                'localhost'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_NAME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_NAME,
                'Database name',
                'magento2'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_USER,
                'Database server username',
                'root'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_ENGINE,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_ENGINE,
                'Database server engine',
                'innodb'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_PASSWORD,
                'Database server password',
                ''
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX,
                'Database table prefix',
                ''
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_MODEL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_MODEL,
                'Database type',
                'mysql4'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_INIT_STATEMENTS,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT .
                '/' . ConfigOptionsListConstants::KEY_INIT_STATEMENTS,
                'Database  initial set of commands',
                'SET NAMES utf8;'
            ),
            new FlagConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION,
                '',
                'If specified, then db connection validation will be skipped',
                '-s'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS,
                'http Cache hosts'
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_SSL_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS .
                '/' . ConfigOptionsListConstants::KEY_MYSQL_SSL_KEY,
                'Full path of client key file in order to establish db connection through SSL',
                ''
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_SSL_CERT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS .
                '/' . ConfigOptionsListConstants::KEY_MYSQL_SSL_CERT,
                'Full path of client certificate file in order to establish db connection through SSL',
                ''
            ),
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_SSL_CA,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS .
                '/' . ConfigOptionsListConstants::KEY_MYSQL_SSL_CA,
                'Full path of server certificate file in order to establish db connection through SSL',
                ''
            ),
            new FlagConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DB_SSL_VERIFY,
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT_DRIVER_OPTIONS .
                '/' . ConfigOptionsListConstants::KEY_MYSQL_SSL_VERIFY,
                'Verify server certification'
            ),
        ];

        foreach ($this->configOptionsCollection as $configOptionsList) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $options = array_merge($options, $configOptionsList->getOptions());
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = [];
        $configData[] = $this->configGenerator->createCryptConfig($data, $deploymentConfig);
        $definitionConfig = $this->configGenerator->createDefinitionsConfig($data);
        if (isset($definitionConfig)) {
            $configData[] = $definitionConfig;
        }
        $configData[] = $this->configGenerator->createDbConfig($data);
        $configData[] = $this->configGenerator->createResourceConfig();
        $configData[] = $this->configGenerator->createXFrameConfig();
        $configData[] = $this->configGenerator->createModeConfig();
        $configData[] = $this->configGenerator->createCacheHostsConfig($data);

        foreach ($this->configOptionsCollection as $configOptionsList) {
            $configData[] = $configOptionsList->createConfig($data, $deploymentConfig);
        }

        return $configData;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS])) {
            $errors = array_merge(
                $errors,
                $this->validateHttpCacheHosts($options[ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS])
            );
        }

        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX])) {
            $errors = array_merge(
                $errors,
                $this->validateDbPrefix($options[ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX])
            );
        }

        if (!$options[ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION]) {
            $errors = array_merge($errors, $this->validateDbSettings($options, $deploymentConfig));
        }

        foreach ($this->configOptionsCollection as $configOptionsList) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $errors = array_merge($errors, $configOptionsList->validate($options, $deploymentConfig));
        }

        $errors = array_merge(
            $errors,
            $this->validateEncryptionKey($options)
        );

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
     * Validates encryption key param
     *
     * @param array $options
     * @return string[]
     */
    private function validateEncryptionKey(array $options)
    {
        $errors = [];

        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY])
            && !$this->encryptionKeyValidator->isValid($options[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY])
        ) {
            $errors[] = 'Invalid encryption key. Encryption key must be 32 character string without any white space.';
        }

        return $errors;
    }

    /**
     * Validate http cache hosts
     *
     * @param string $option
     * @return string[]
     */
    private function validateHttpCacheHosts($option)
    {
        $errors = [];
        if (!preg_match('/^[\-\w:,.]+$/', $option)
        ) {
            $errors[] = "Invalid http cache hosts '{$option}'";
        }
        return $errors;
    }

    /**
     * Validate Db table prefix
     *
     * @param string $option
     * @return string[]
     */
    private function validateDbPrefix($option)
    {
        $errors = [];
        try {
            $this->dbValidator->checkDatabaseTablePrefix($option);
        } catch (\InvalidArgumentException $exception) {
            $errors[] = $exception->getMessage();
        }
        return $errors;
    }

    /**
     * Validate Db settings
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return string[]
     */
    private function validateDbSettings(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if ($options[ConfigOptionsListConstants::INPUT_KEY_DB_NAME] !== null
            || $options[ConfigOptionsListConstants::INPUT_KEY_DB_HOST] !== null
            || $options[ConfigOptionsListConstants::INPUT_KEY_DB_USER] !== null
            || $options[ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD] !== null
        ) {
            try {
                $options = $this->getDbSettings($options, $deploymentConfig);
                $driverOptions = $this->driverOptions->getDriverOptions($options);

                $this->dbValidator->checkDatabaseConnectionWithDriverOptions(
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_NAME],
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_HOST],
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_USER],
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD],
                    $driverOptions
                );
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }
        return $errors;
    }
}
