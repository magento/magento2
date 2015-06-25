<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**
     * Generate config data for individual segments
     *
     * @var ConfigGenerator
     */
    private $configGenerator;

    /**
     * @var DbValidator
     */
    private $dbValidator;

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
                [ConfigOptionsListConstants::SESSION_SAVE_FILES, ConfigOptionsListConstants::SESSION_SAVE_DB],
                ConfigOptionsListConstants::CONFIG_PATH_SESSION_SAVE,
                'Session save location',
                ConfigOptionsListConstants::SESSION_SAVE_FILES
            ),
            new SelectConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_DEFINITION_FORMAT,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                DefinitionFactory::getSupportedFormats(),
                ConfigOptionsListConstants::CONFIG_PATH_DEFINITION_FORMAT,
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
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data, DeploymentConfig $deploymentConfig)
    {
        $configData = [];
        $configData[] = $this->configGenerator->createInstallConfig($deploymentConfig);
        $configData[] = $this->configGenerator->createCryptConfig($data, $deploymentConfig);
        $configData[] = $this->configGenerator->createSessionConfig($data);
        $definitionConfig = $this->configGenerator->createDefinitionsConfig($data);
        if (isset($definitionConfig)) {
            $configData[] = $definitionConfig;
        }
        $configData[] = $this->configGenerator->createDbConfig($data);
        $configData[] = $this->configGenerator->createResourceConfig();
        $configData[] = $this->configGenerator->createXFrameConfig();
        return $configData;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX])) {
            try {
                $this->dbValidator->checkDatabaseTablePrefix($options[ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX]);
            } catch (\InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if (!$options[ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION] &&
            (
                $options[ConfigOptionsListConstants::INPUT_KEY_DB_NAME] !== null
                || $options[ConfigOptionsListConstants::INPUT_KEY_DB_HOST] !== null
                || $options[ConfigOptionsListConstants::INPUT_KEY_DB_USER] !== null
                || $options[ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD] !== null
            )
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
