<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManager\DefinitionFactory;
use Magento\Framework\Setup\ConfigOptionsInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\DeploymentConfig\EncryptConfig;
use Magento\Framework\App\DeploymentConfig\SessionConfig;

/**
 * Deployment configuration options needed for Setup application
 */
class ConfigOptions implements ConfigOptionsInterface
{
    /**#@+
     * Path to the values in the deployment config
     */
    const CONFIG_PATH_INSTALL_DATE = 'install/date';
    /**#@-*/

    /**#@+
     * Input keys for the options
     */
    const INPUT_KEY_CRYPT_KEY = 'key';
    const INPUT_KEY_SESSION_SAVE = 'session_save';
    const INPUT_KEY_DEFINITION_FORMAT = 'definition_format';
    const INPUT_KEY_DB_HOST = 'db_host';
    const INPUT_KEY_DB_NAME = 'db_name';
    const INPUT_KEY_DB_USER = 'db_user';
    const INPUT_KEY_DB_PASS = 'db_pass';
    const INPUT_KEY_DB_PREFIX = 'db_prefix';
    const INPUT_KEY_DB_MODEL = 'db_model';
    const INPUT_KEY_DB_INIT_STATEMENTS = 'db_init_statements';
    /**#@-*/

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var array
     */
    private $moduleList;

    /**
     * @var Random
     */
    private $random;

    /**
     * Maps configuration parameters to array keys in deployment config file
     *
     * @var array
     */
    public static $paramMap = [
        self::INPUT_KEY_DB_HOST => DbConfig::KEY_HOST,
        self::INPUT_KEY_DB_NAME => DbConfig::KEY_NAME,
        self::INPUT_KEY_DB_USER => DbConfig::KEY_USER,
        self::INPUT_KEY_DB_PASS => DbConfig::KEY_PASS,
        self::INPUT_KEY_DB_PREFIX => DbConfig::KEY_PREFIX,
        self::INPUT_KEY_DB_MODEL => DbConfig::KEY_MODEL,
        self::INPUT_KEY_DB_INIT_STATEMENTS => DbConfig::KEY_INIT_STATEMENTS,
    ];

    /**
     * Constructor
     *
     * @param Random $random
     * @param Loader $moduleLoader
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(Random $random, Loader $moduleLoader, DeploymentConfig $deploymentConfig)
    {
        $this->random = $random;
        $this->deploymentConfig = $deploymentConfig;
        $this->moduleList = array_keys($moduleLoader->load());
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_CRYPT_KEY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Encryption key'
            ),
            new SelectConfigOption(
                self::INPUT_KEY_SESSION_SAVE,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                ['files', 'db'],
                'Session save location',
                'files'
            ),
            new SelectConfigOption(
                self::INPUT_KEY_DEFINITION_FORMAT,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                DefinitionFactory::getSupportedFormats(),
                'Type of definitions used by Object Manager'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Database server host'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_NAME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Database name'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Database server username'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_PASS,
                TextConfigOption::FRONTEND_WIZARD_PASSWORD,
                'Database server password'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Database table prefix'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_MODEL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Database type'
            ),
            new TextConfigOption(
                self::INPUT_KEY_DB_INIT_STATEMENTS,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                'Database  initial set of commands'
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $data)
    {
        $configData = [];
        // install segment
        $configData[] = new ConfigData(
            ConfigFilePool::APP_CONFIG,
            'install',
            [DeploymentConfigMapper::$paramMap[DeploymentConfigMapper::KEY_DATE] => date('r')]
        );

        // crypt segment
        if (isset($data[self::INPUT_KEY_CRYPT_KEY]) && !$data[self::INPUT_KEY_CRYPT_KEY]) {
            throw new \InvalidArgumentException('Invalid encryption key.');
        }
        $cryptData = [];
        if (!isset($data[self::INPUT_KEY_CRYPT_KEY])) {
            $cryptData[DeploymentConfigMapper::$paramMap[self::INPUT_KEY_CRYPT_KEY]] =
                md5($this->random->getRandomString(10));
        } else {
            $cryptData[DeploymentConfigMapper::$paramMap[self::INPUT_KEY_CRYPT_KEY]] = $data[self::INPUT_KEY_CRYPT_KEY];
        }
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'crypt', $cryptData);

        // module segment
        if (!$this->deploymentConfig->isAvailable()) {
            $modulesData = [];
            if (isset($this->moduleList)) {
                foreach (array_values($this->moduleList) as $key) {
                    $modulesData[$key] = 1;
                }
            }
            $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'modules', $modulesData);
        }

        // session segment
        $sessionData = [];
        if (isset($data[self::INPUT_KEY_SESSION_SAVE])) {
            if ($data[self::INPUT_KEY_SESSION_SAVE] != 'files' && $data[self::INPUT_KEY_SESSION_SAVE] != 'db') {
                throw new \InvalidArgumentException('Invalid session save location.');
            }
            $sessionData[DeploymentConfigMapper::$paramMap[self::INPUT_KEY_SESSION_SAVE]] =
                $data[self::INPUT_KEY_SESSION_SAVE];
        } else {
            $sessionData[DeploymentConfigMapper::$paramMap[self::INPUT_KEY_SESSION_SAVE]] = 'files';
        }
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'session', $sessionData);

        // definitions segment
        if (!empty($data[self::INPUT_KEY_DEFINITION_FORMAT])) {
            $config['definition']['format'] = $data[self::INPUT_KEY_DEFINITION_FORMAT];
            $configData[] = new ConfigData(
                ConfigFilePool::APP_CONFIG,
                'definition',
                ['format' => $data[self::INPUT_KEY_DEFINITION_FORMAT]]
            );
        }

        // db segment
        $connection = [];
        $required = [self::INPUT_KEY_DB_HOST, self::INPUT_KEY_DB_NAME, self::INPUT_KEY_DB_USER];
        foreach ($required as $key) {
            if (!isset($data[$key])) {
                throw new \InvalidArgumentException("Missing value for db configuration: {$key}");
            }
            $connection[self::$paramMap[$key]] = $data[$key];
        }
        $optional = [self::INPUT_KEY_DB_INIT_STATEMENTS, self::INPUT_KEY_DB_MODEL, self::INPUT_KEY_DB_PASS];
        foreach ($optional as $key) {
            $connection[self::$paramMap[$key]] = isset($data[$key]) ? $data[$key] : null;
        }
        $prefixKey = self::INPUT_KEY_DB_PREFIX;
        $dbData = [
            DeploymentConfigMapper::$paramMap[$prefixKey] => isset($data[$prefixKey]) ? $data[$prefixKey] : null,
            'connection' => ['default' => $connection],
        ];
        $configData[] = new ConfigData(ConfigFilePool::APP_CONFIG, 'db', $dbData);

        return $configData;
    }
}
