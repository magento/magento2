<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Math\Random;
use Magento\Framework\Module\ModuleList\Loader;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\DbConfig;
use Magento\Framework\App\DeploymentConfig\EncryptConfig;
use Magento\Framework\App\DeploymentConfig\InstallConfig;
use Magento\Framework\App\DeploymentConfig\SessionConfig;
use Magento\Framework\App\DeploymentConfig\ResourceConfig;

/**
 * Creates deployment config data based on user input array
 */
class ConfigGenerator
{
    /**
     * Maps configuration parameters to array keys in deployment config file
     *
     * @var array
     */
    public static $paramMap = [
        ConfigOptionsList::INPUT_KEY_DB_HOST => DbConfig::KEY_HOST,
        ConfigOptionsList::INPUT_KEY_DB_NAME => DbConfig::KEY_NAME,
        ConfigOptionsList::INPUT_KEY_DB_USER => DbConfig::KEY_USER,
        ConfigOptionsList::INPUT_KEY_DB_PASS => DbConfig::KEY_PASS,
        ConfigOptionsList::INPUT_KEY_DB_PREFIX => DbConfig::KEY_PREFIX,
        ConfigOptionsList::INPUT_KEY_DB_MODEL => DbConfig::KEY_MODEL,
        ConfigOptionsList::INPUT_KEY_DB_INIT_STATEMENTS => DbConfig::KEY_INIT_STATEMENTS,
        ConfigOptionsList::INPUT_KEY_ACTIVE => DbConfig::KEY_ACTIVE,
        ConfigOptionsList::INPUT_KEY_CRYPT_KEY => EncryptConfig::KEY_ENCRYPTION_KEY,
        ConfigOptionsList::INPUT_KEY_SESSION_SAVE => SessionConfig::KEY_SAVE,
        ConfigOptionsList::INPUT_KEY_RESOURCE => ResourceConfig::CONFIG_KEY,
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
     * Creates install segment config data
     *
     * @return ConfigData
     */
    public function createInstallConfig()
    {
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'install', [InstallConfig::KEY_DATE => date('r')]);
    }

    /**
     * Creates encryption key config data
     * @param array $data
     * @return ConfigData
     */
    public function createCryptConfig(array $data)
    {
        $cryptData = [];
        if (!isset($data[ConfigOptionsList::INPUT_KEY_CRYPT_KEY])) {
            $cryptData[self::$paramMap[ConfigOptionsList::INPUT_KEY_CRYPT_KEY]] =
                md5($this->random->getRandomString(10));
        } else {
            $cryptData[self::$paramMap[ConfigOptionsList::INPUT_KEY_CRYPT_KEY]] =
                $data[ConfigOptionsList::INPUT_KEY_CRYPT_KEY];
        }
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'crypt', $cryptData);
    }

    /**
     * Creates module config data
     *
     * @return ConfigData
     */
    public function createModuleConfig()
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $modulesData = [];
            if (isset($this->moduleList)) {
                foreach ($this->moduleList as $key) {
                    $modulesData[$key] = 1;
                }
            }
            return new ConfigData(ConfigFilePool::APP_CONFIG, 'modules', $modulesData);
        }
    }

    /**
     * Creates session config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createSessionConfig(array $data)
    {
        $sessionData = [];
        if (isset($data[ConfigOptionsList::INPUT_KEY_SESSION_SAVE])) {
            $sessionData[self::$paramMap[ConfigOptionsList::INPUT_KEY_SESSION_SAVE]] =
                $data[ConfigOptionsList::INPUT_KEY_SESSION_SAVE];
        } else {
            $sessionData[self::$paramMap[ConfigOptionsList::INPUT_KEY_SESSION_SAVE]] =
                ConfigOptionsList::SESSION_SAVE_FILES;
        }
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'session', $sessionData);
    }

    /**
     * Creates definitions config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createDefinitionsConfig(array $data)
    {
        if (!empty($data[ConfigOptionsList::INPUT_KEY_DEFINITION_FORMAT])) {
            return new ConfigData(
                ConfigFilePool::APP_CONFIG,
                'definition',
                ['format' => $data[ConfigOptionsList::INPUT_KEY_DEFINITION_FORMAT]]
            );
        }
    }

    /**
     * Creates db config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createDbConfig(array $data)
    {
        $connection = [];

        $optional = [
            ConfigOptionsList::INPUT_KEY_DB_HOST,
            ConfigOptionsList::INPUT_KEY_DB_NAME,
            ConfigOptionsList::INPUT_KEY_DB_USER,
            ConfigOptionsList::INPUT_KEY_DB_PASS,
            ConfigOptionsList::INPUT_KEY_DB_MODEL,
            ConfigOptionsList::INPUT_KEY_DB_INIT_STATEMENTS,
        ];

        foreach ($optional as $key) {
            if (isset($data[$key])) {
                $connection[self::$paramMap[$key]] = $data[$key];
            }
        }

        $connection[self::$paramMap[ConfigOptionsList::INPUT_KEY_ACTIVE]] = '1';
        $prefixKey = isset($data[ConfigOptionsList::INPUT_KEY_DB_PREFIX])
            ? $data[ConfigOptionsList::INPUT_KEY_DB_PREFIX]
            : '';
        $dbData = [
            self::$paramMap[ConfigOptionsList::INPUT_KEY_DB_PREFIX] => $prefixKey,
            'connection' => ['default' => $connection]
        ];
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'db', $dbData);
    }

    /**
     * Creates resource config data
     *
     * @return ConfigData
     */
    public function createResourceConfig()
    {
        $resourceData[self::$paramMap[ConfigOptionsList::INPUT_KEY_RESOURCE]] =
            ['default_setup' => ['connection' => 'default']];
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'resource', $resourceData);
    }
}
