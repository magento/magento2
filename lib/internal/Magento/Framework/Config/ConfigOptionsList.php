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
use Magento\Framework\App\DeploymentConfig;

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
    const INPUT_KEY_DB_PASS = 'db_pass';
    const INPUT_KEY_DB_PREFIX = 'db_prefix';
    const INPUT_KEY_DB_MODEL = 'db_model';
    const INPUT_KEY_DB_INIT_STATEMENTS = 'db_init_statements';
    const INPUT_KEY_RESOURCE = 'resource';
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
    const KEY_PASS = 'password';
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
                self::INPUT_KEY_DB_PASS,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                self::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::KEY_PASS,
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
    public function validate(array $options)
    {
        $errors = [];

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
}
