<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\Framework\App\DeploymentConfig;

/**
 * Creates deployment config data based on user input array
 * this class introduced to break down Magento\Framework\Config\ConfigOptionsList::createConfig
 */
class ConfigGenerator
{
    /**
     * Maps configuration parameters to array keys in deployment config file
     *
     * @var array
     */
    private static $paramMap = [
        ConfigOptionsList::INPUT_KEY_DB_HOST => ConfigOptionsList::KEY_HOST,
        ConfigOptionsList::INPUT_KEY_DB_NAME => ConfigOptionsList::KEY_NAME,
        ConfigOptionsList::INPUT_KEY_DB_USER => ConfigOptionsList::KEY_USER,
        ConfigOptionsList::INPUT_KEY_DB_PASS => ConfigOptionsList::KEY_PASS,
        ConfigOptionsList::INPUT_KEY_DB_PREFIX => ConfigOptionsList::KEY_PREFIX,
        ConfigOptionsList::INPUT_KEY_DB_MODEL => ConfigOptionsList::KEY_MODEL,
        ConfigOptionsList::INPUT_KEY_DB_INIT_STATEMENTS => ConfigOptionsList::KEY_INIT_STATEMENTS,
        ConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY => ConfigOptionsList::KEY_ENCRYPTION_KEY,
        ConfigOptionsList::INPUT_KEY_SESSION_SAVE => ConfigOptionsList::KEY_SAVE,
        ConfigOptionsList::INPUT_KEY_RESOURCE => ConfigOptionsList::KEY_RESOURCE,
    ];

    /**
     * @var Random
     */
    protected $random;

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * Constructor
     *
     * @param Random $random
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(Random $random, DeploymentConfig $deploymentConfig)
    {
        $this->random = $random;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Creates install segment config data
     *
     * @return ConfigData
     */
    public function createInstallConfig()
    {
        $configData = new ConfigData(ConfigFilePool::APP_CONFIG);

        if ($this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_INSTALL_DATE) === null) {
            $configData->set(ConfigOptionsList::CONFIG_PATH_INSTALL_DATE, date('r'));
        }
        return $configData;
    }

    /**
     * Creates encryption key config data
     * @param array $data
     * @return ConfigData
     */
    public function createCryptConfig(array $data)
    {
        $currentKey = $this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_CRYPT_KEY);

        $configData = new ConfigData(ConfigFilePool::APP_CONFIG);
        if (isset($data[ConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY])) {
            if ($currentKey !== null) {
                $key = $currentKey . "\n" . $data[ConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY];
            } else {
                $key = $data[ConfigOptionsList::INPUT_KEY_ENCRYPTION_KEY];
            }

            $configData->set(ConfigOptionsList::CONFIG_PATH_CRYPT_KEY, $key);
        } else {
            if ($currentKey === null) {
                $configData->set(ConfigOptionsList::CONFIG_PATH_CRYPT_KEY, md5($this->random->getRandomString(10)));
            }
        }

        return $configData;
    }

    /**
     * Creates session config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createSessionConfig(array $data)
    {
        $configData = new ConfigData(ConfigFilePool::APP_CONFIG);

        if (isset($data[ConfigOptionsList::INPUT_KEY_SESSION_SAVE])) {
            $configData->set(
                ConfigOptionsList::CONFIG_PATH_SESSION_SAVE,
                $data[ConfigOptionsList::INPUT_KEY_SESSION_SAVE]
            );
        }

        return $configData;
    }

    /**
     * Creates definitions config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createDefinitionsConfig(array $data)
    {
        $configData = new ConfigData(ConfigFilePool::APP_CONFIG);

        if (!empty($data[ConfigOptionsList::INPUT_KEY_DEFINITION_FORMAT])) {
            $configData->set(
                ConfigOptionsList::CONFIG_PATH_DEFINITION_FORMAT,
                $data[ConfigOptionsList::INPUT_KEY_DEFINITION_FORMAT]
            );
        }

        return $configData;
    }

    /**
     * Creates db config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createDbConfig(array $data)
    {
        $configData = new ConfigData(ConfigFilePool::APP_CONFIG);

        $optional = [
            ConfigOptionsList::INPUT_KEY_DB_HOST,
            ConfigOptionsList::INPUT_KEY_DB_NAME,
            ConfigOptionsList::INPUT_KEY_DB_USER,
            ConfigOptionsList::INPUT_KEY_DB_PASS,
            ConfigOptionsList::INPUT_KEY_DB_MODEL,
            ConfigOptionsList::INPUT_KEY_DB_INIT_STATEMENTS,
        ];

        if (isset($data[ConfigOptionsList::INPUT_KEY_DB_PREFIX])) {
            $configData->set(ConfigOptionsList::CONFIG_PATH_DB_PREFIX, $data[ConfigOptionsList::INPUT_KEY_DB_PREFIX]);
        }

        foreach ($optional as $key) {
            if (isset($data[$key])) {
                $configData->set(
                    ConfigOptionsList::CONFIG_PATH_DB_CONNECTION_DEFAULT . self::$paramMap[$key],
                    $data[$key]
                );
            }
        }

        $currentStatus = $this->deploymentConfig->get(
            ConfigOptionsList::CONFIG_PATH_DB_CONNECTION_DEFAULT . ConfigOptionsList::KEY_ACTIVE
        );

        if ($currentStatus === null) {
            $configData->set(ConfigOptionsList::CONFIG_PATH_DB_CONNECTION_DEFAULT . ConfigOptionsList::KEY_ACTIVE, '1');
        }

        return $configData;
    }

    /**
     * Creates resource config data
     *
     * @return ConfigData
     */
    public function createResourceConfig()
    {
        $configData = new ConfigData(ConfigFilePool::APP_CONFIG);

        if ($this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_RESOURCE_DEFAULT_SETUP) === null) {
            $configData->set(ConfigOptionsList::CONFIG_PATH_RESOURCE_DEFAULT_SETUP, 'default');
        }

        return $configData;
    }
}
