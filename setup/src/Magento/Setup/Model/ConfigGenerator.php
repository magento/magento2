<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManagerFactory;

/**
 * Creates deployment config data based on user input array
 * this class introduced to break down Magento\Setup\Model\ConfigOptionsList::createConfig
 */
class ConfigGenerator
{
    /**
     * Maps configuration parameters to array keys in deployment config file
     *
     * @var array
     */
    private static $paramMap = [
        ConfigOptionsListConstants::INPUT_KEY_DB_HOST => ConfigOptionsListConstants::KEY_HOST,
        ConfigOptionsListConstants::INPUT_KEY_DB_NAME => ConfigOptionsListConstants::KEY_NAME,
        ConfigOptionsListConstants::INPUT_KEY_DB_USER => ConfigOptionsListConstants::KEY_USER,
        ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD => ConfigOptionsListConstants::KEY_PASSWORD,
        ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX => ConfigOptionsListConstants::KEY_PREFIX,
        ConfigOptionsListConstants::INPUT_KEY_DB_MODEL => ConfigOptionsListConstants::KEY_MODEL,
        ConfigOptionsListConstants::INPUT_KEY_DB_ENGINE => ConfigOptionsListConstants::KEY_ENGINE,
        ConfigOptionsListConstants::INPUT_KEY_DB_INIT_STATEMENTS => ConfigOptionsListConstants::KEY_INIT_STATEMENTS,
        ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY => ConfigOptionsListConstants::KEY_ENCRYPTION_KEY,
        ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE => ConfigOptionsListConstants::KEY_SAVE,
        ConfigOptionsListConstants::INPUT_KEY_RESOURCE => ConfigOptionsListConstants::KEY_RESOURCE,
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
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if ($this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE) === null) {
            $configData->set(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE, date('r'));
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
        $currentKey = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);

        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if (isset($data[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY])) {
            if ($currentKey !== null) {
                $key = $currentKey . "\n" . $data[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY];
            } else {
                $key = $data[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY];
            }

            $configData->set(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY, $key);
        } else {
            if ($currentKey === null) {
                $configData->set(
                    ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY,
                    md5($this->random->getRandomString(ConfigOptionsListConstants::STORE_KEY_RANDOM_STRING_SIZE))
                );
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
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (isset($data[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE])) {
            $configData->set(
                ConfigOptionsListConstants::CONFIG_PATH_SESSION_SAVE,
                $data[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]
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
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (!empty($data[ConfigOptionsListConstants::INPUT_KEY_DEFINITION_FORMAT])) {
            $configData->set(
                ObjectManagerFactory::CONFIG_PATH_DEFINITION_FORMAT,
                $data[ConfigOptionsListConstants::INPUT_KEY_DEFINITION_FORMAT]
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
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        $optional = [
            ConfigOptionsListConstants::INPUT_KEY_DB_HOST,
            ConfigOptionsListConstants::INPUT_KEY_DB_NAME,
            ConfigOptionsListConstants::INPUT_KEY_DB_USER,
            ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD,
            ConfigOptionsListConstants::INPUT_KEY_DB_MODEL,
            ConfigOptionsListConstants::INPUT_KEY_DB_ENGINE,
            ConfigOptionsListConstants::INPUT_KEY_DB_INIT_STATEMENTS,
        ];

        if (isset($data[ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX])) {
            $configData->set(
                ConfigOptionsListConstants::CONFIG_PATH_DB_PREFIX,
                $data[ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX]
            );
        }

        foreach ($optional as $key) {
            if (isset($data[$key])) {
                $configData->set(
                    ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/' . self::$paramMap[$key],
                    $data[$key]
                );
            }
        }

        $currentStatus = $this->deploymentConfig->get(
            ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/' . ConfigOptionsListConstants::KEY_ACTIVE
        );

        if ($currentStatus === null) {
            $configData->set(
                ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT
                . '/' . ConfigOptionsListConstants::KEY_ACTIVE,
                '1'
            );
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
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if ($this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_RESOURCE_DEFAULT_SETUP) === null) {
            $configData->set(ConfigOptionsListConstants::CONFIG_PATH_RESOURCE_DEFAULT_SETUP, 'default');
        }

        return $configData;
    }

    /**
     * Creates x-frame-options header config data
     *
     * @return ConfigData
     */
    public function createXFrameConfig()
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if ($this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT) === null) {
            $configData->set(ConfigOptionsListConstants::CONFIG_PATH_X_FRAME_OPT, 'SAMEORIGIN');
        }
        return $configData;
    }

    /**
     * Create default entry for mode config option
     *
     * @return ConfigData
     */
    public function createModeConfig()
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        $configData->set(State::PARAM_MODE, State::MODE_DEFAULT);
        return $configData;
    }

    /**
     * Creates cache hosts config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createCacheHostsConfig(array $data)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if (isset($data[ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS])) {
            $hostData = explode(',', $data[ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS]);
            $hosts = [];
            foreach ($hostData as $data) {
                $dataArray = explode(':', trim($data));
                $host = [];
                $host['host'] = $dataArray[0];
                if (isset($dataArray[1])) {
                    $host['port'] = $dataArray[1];
                }
                $hosts[] = $host;
            }
            $configData->set(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS, $hosts);
        }
        return $configData;
    }
}
