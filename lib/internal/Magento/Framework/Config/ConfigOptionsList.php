<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\Setup\Option\FlagConfigOption;
use Magento\Framework\App\DeploymentConfig;
use Magento\Setup\Validator\DbValidator;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptionsList implements ConfigOptionsListInterface
{
    /**#@+
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_INSTALL_DATE = 'install/date';
    const CONFIG_PATH_CRYPT_KEY = 'crypt/key';
    const CONFIG_PATH_SESSION_SAVE = 'session/save';
    const CONFIG_PATH_DEFINITION_FORMAT = 'definition/format';
    const CONFIG_PATH_RESOURCE_DEFAULT_SETUP = 'resource/default_setup/connection';
    const CONFIG_PATH_DB_CONNECTION_DEFAULT = 'db/connection/default/';
    const CONFIG_PATH_DB_PREFIX = 'db/table_prefix';
    /**#@-*/

    /**#@+
     * Input keys for the options
     */
    const INPUT_KEY_ENCRYPTION_KEY = 'key';
    const INPUT_KEY_SESSION_SAVE = 'session_save';
    const INPUT_KEY_DEFINITION_FORMAT = 'definition_format';
    const INPUT_KEY_DB_HOST = 'db_host';
    const INPUT_KEY_DB_NAME = 'db_name';
    const INPUT_KEY_DB_USER = 'db_user';
    const INPUT_KEY_DB_PASSWORD = 'db_password';
    const INPUT_KEY_DB_PREFIX = 'db_prefix';
    const INPUT_KEY_DB_MODEL = 'db_model';
    const INPUT_KEY_DB_INIT_STATEMENTS = 'db_init_statements';
    const INPUT_KEY_RESOURCE = 'resource';
    const INPUT_KEY_SKIP_DB_VALIDATION = 'skip-db-validation';
    /**#@-*/

    /**#@+
     * Values for session_save
     */
    const SESSION_SAVE_FILES = 'files';
    const SESSION_SAVE_DB = 'db';
    /**#@-*/

    /**
     * Array Key for session save method
     */
    const KEY_SAVE = 'save';

    /**#@+
     * Array keys for Database configuration
     */
    const KEY_HOST = 'host';
    const KEY_NAME = 'dbname';
    const KEY_USER = 'username';
    const KEY_PASSWORD = 'password';
    const KEY_PREFIX = 'table_prefix';
    const KEY_MODEL = 'model';
    const KEY_INIT_STATEMENTS = 'initStatements';
    const KEY_ACTIVE = 'active';
    /**#@-*/
    
    /**
     * Db config key
     */
    const KEY_DB = 'db';

    /**
     * Array Key for encryption key in deployment config file
     */
    const KEY_ENCRYPTION_KEY = 'key';

    /**
     * Resource config key
     */
    const KEY_RESOURCE = 'resource';

    /**
     * Key for modules
     */
    const KEY_MODULES = 'modules';

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
                self::INPUT_KEY_ENCRYPTION_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CRYPT_KEY,
                'Encryption key'
            ),
            new SelectConfigOption(
                self::INPUT_KEY_SESSION_SAVE,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                [self::SESSION_SAVE_FILES, self::SESSION_SAVE_DB],
                self::CONFIG_PATH_SESSION_SAVE,
                'Session save location',
                self::SESSION_SAVE_FILES
            ),
            new SelectConfigOption(
                self::INPUT_KEY_DEFINITION_FORMAT,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                DefinitionFactory::getSupportedFormats(),
                self::CONFIG_PATH_DEFINITION_FORMAT,
                'Type of definitions used by Object Manager'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_HOST,
                'Database server host',
                'localhost'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_NAME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_NAME,
                'Database name',
                'magento2'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_USER,
                'Database server username',
                'root'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_PASSWORD,
                'Database server password',
                ''
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_DB_PREFIX,
                'Database table prefix',
                ''
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_MODEL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_MODEL,
                'Database type',
                'mysql4'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_INIT_STATEMENTS,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_INIT_STATEMENTS,
                'Database  initial set of commands',
                'SET NAMES utf8;'
            ),
            new FlagConfigOption(
                self::INPUT_KEY_SKIP_DB_VALIDATION,
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
        return $configData;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        if (isset($options[ConfigOptionsList::INPUT_KEY_DB_PREFIX])) {
            try {
                $this->dbValidator->checkDatabaseTablePrefix($options[ConfigOptionsList::INPUT_KEY_DB_PREFIX]);
            } catch (\InvalidArgumentException $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if (!$options[ConfigOptionsList::INPUT_KEY_SKIP_DB_VALIDATION] &&
            (
                $options[ConfigOptionsList::INPUT_KEY_DB_NAME] !== null
                || $options[ConfigOptionsList::INPUT_KEY_DB_HOST] !== null
                || $options[ConfigOptionsList::INPUT_KEY_DB_USER] !== null
                || $options[ConfigOptionsList::INPUT_KEY_DB_PASSWORD] !== null
            )
        ) {
            try {

                $options = $this->getDbSettings($options, $deploymentConfig);

                $this->dbValidator->checkDatabaseConnection(
                    $options[ConfigOptionsList::INPUT_KEY_DB_NAME],
                    $options[ConfigOptionsList::INPUT_KEY_DB_HOST],
                    $options[ConfigOptionsList::INPUT_KEY_DB_USER],
                    $options[ConfigOptionsList::INPUT_KEY_DB_PASSWORD]
                );
            } catch (\Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        if (isset($options[ConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY])
            && !$options[ConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY]) {
            $errors[] = 'Invalid encryption key.';
        }

        if (isset($options[ConfigOptionsList::INPUT_KEY_SESSION_SAVE])) {
            if ($options[ConfigOptionsList::INPUT_KEY_SESSION_SAVE] != ConfigOptionsList::SESSION_SAVE_FILES &&
                $options[ConfigOptionsList::INPUT_KEY_SESSION_SAVE] != ConfigOptionsList::SESSION_SAVE_DB
            ) {
                $errors[] = 'Invalid session save location.';
            }
        }

        return $errors;
    }

    /**
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     *
     * @return array
     */
    private function getDbSettings(array $options, DeploymentConfig $deploymentConfig)
    {
        if ($options[ConfigOptionsList::INPUT_KEY_DB_NAME] === null) {
            $options[ConfigOptionsList::INPUT_KEY_DB_NAME] =
                $deploymentConfig->get(self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_NAME);
        }

        if ($options[ConfigOptionsList::INPUT_KEY_DB_HOST] === null) {
            $options[ConfigOptionsList::INPUT_KEY_DB_HOST] =
                $deploymentConfig->get(self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_HOST);
        }

        if ($options[ConfigOptionsList::INPUT_KEY_DB_USER] === null) {
            $options[ConfigOptionsList::INPUT_KEY_DB_USER] =
                $deploymentConfig->get(self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_USER);
        }

        if ($options[ConfigOptionsList::INPUT_KEY_DB_PASSWORD] === null) {
            $options[ConfigOptionsList::INPUT_KEY_DB_PASSWORD] =
                $deploymentConfig->get(self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_PASSWORD);
        }

        return $options;
    }
}
