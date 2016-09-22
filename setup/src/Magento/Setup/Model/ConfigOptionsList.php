<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\Setup\Option\FlagConfigOption;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Setup\Validator\DbValidator;
use Magento\Framework\App\ObjectManagerFactory;

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

    /** @var  DbValidator */
    private $dbValidator;

    /** @var  array */
    private $validSaveHandlers = [
        ConfigOptionsListConstants::SESSION_SAVE_FILES,
        ConfigOptionsListConstants::SESSION_SAVE_DB,
    ];

    /**
     * Constructor
     *
     * @param ConfigGenerator $configGenerator
     * @param DbValidator $dbValidator
     */
    public function __construct(ConfigGenerator $configGenerator, DbValidator $dbValidator)
    {
        $this->configGenerator = $configGenerator;
        $this->dbValidator = $dbValidator;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY,
                'Encryption key'
            ),
            new SelectConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->validSaveHandlers,
                ConfigOptionsListConstants::CONFIG_PATH_SESSION_SAVE,
                'Session save handler',
                ConfigOptionsListConstants::SESSION_SAVE_FILES
            ),
            new SelectConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DEFINITION_FORMAT,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                DefinitionFactory::getSupportedFormats(),
                ObjectManagerFactory::CONFIG_PATH_DEFINITION_FORMAT,
                'Type of definitions used by Object Manager'
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = [];
        $configData[] = $this->configGenerator->createCryptConfig($data, $deploymentConfig);
        $configData[] = $this->configGenerator->createSessionConfig($data);
        $definitionConfig = $this->configGenerator->createDefinitionsConfig($data);
        if (isset($definitionConfig)) {
            $configData[] = $definitionConfig;
        }
        $configData[] = $this->configGenerator->createDbConfig($data);
        $configData[] = $this->configGenerator->createResourceConfig();
        $configData[] = $this->configGenerator->createXFrameConfig();
        $configData[] = $this->configGenerator->createModeConfig();
        $configData[] = $this->configGenerator->createCacheHostsConfig($data);
        return $configData;
    }

    /**
     * {@inheritdoc}
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

        $errors = array_merge(
            $errors,
            $this->validateSessionSave($options),
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
     * Validates session save param
     *
     * @param array $options
     * @return string[]
     */
    private function validateSessionSave(array $options)
    {
        $errors = [];
        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE])
            && !in_array($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE], $this->validSaveHandlers)
        ) {
            $errors[] = "Invalid session handler '{$options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]}'";
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
            $errors[] = 'Invalid encryption key';
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

                $this->dbValidator->checkDatabaseConnection(
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_NAME],
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_HOST],
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_USER],
                    $options[ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD]
                );
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }
        return $errors;
    }
}
