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
        ConfigOptions::INPUT_KEY_DB_HOST => DbConfig::KEY_HOST,
        ConfigOptions::INPUT_KEY_DB_NAME => DbConfig::KEY_NAME,
        ConfigOptions::INPUT_KEY_DB_USER => DbConfig::KEY_USER,
        ConfigOptions::INPUT_KEY_DB_PASS => DbConfig::KEY_PASS,
        ConfigOptions::INPUT_KEY_DB_PREFIX => DbConfig::KEY_PREFIX,
        ConfigOptions::INPUT_KEY_DB_MODEL => DbConfig::KEY_MODEL,
        ConfigOptions::INPUT_KEY_DB_INIT_STATEMENTS => DbConfig::KEY_INIT_STATEMENTS,
        ConfigOptions::INPUT_KEY_ACTIVE => DbConfig::KEY_ACTIVE,
        ConfigOptions::INPUT_KEY_CRYPT_KEY => EncryptConfig::KEY_ENCRYPTION_KEY,
        ConfigOptions::INPUT_KEY_SESSION_SAVE => SessionConfig::KEY_SAVE,
        ConfigOptions::INPUT_KEY_RESOURCE => ResourceConfig::CONFIG_KEY,
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
        if (!isset($data[ConfigOptions::INPUT_KEY_CRYPT_KEY])) {
            $cryptData[self::$paramMap[ConfigOptions::INPUT_KEY_CRYPT_KEY]] = md5($this->random->getRandomString(10));
        } else {
            $cryptData[self::$paramMap[ConfigOptions::INPUT_KEY_CRYPT_KEY]] = $data[ConfigOptions::INPUT_KEY_CRYPT_KEY];
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
        if (isset($data[ConfigOptions::INPUT_KEY_SESSION_SAVE])) {
            $sessionData[self::$paramMap[ConfigOptions::INPUT_KEY_SESSION_SAVE]] =
                $data[ConfigOptions::INPUT_KEY_SESSION_SAVE];
        } else {
            $sessionData[self::$paramMap[ConfigOptions::INPUT_KEY_SESSION_SAVE]] = ConfigOptions::SESSION_SAVE_FILES;
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
        if (!empty($data[ConfigOptions::INPUT_KEY_DEFINITION_FORMAT])) {
            $config['definition']['format'] = $data[ConfigOptions::INPUT_KEY_DEFINITION_FORMAT];
            return new ConfigData(
                ConfigFilePool::APP_CONFIG,
                'definition',
                ['format' => $data[ConfigOptions::INPUT_KEY_DEFINITION_FORMAT]]
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

        $required = [
            ConfigOptions::INPUT_KEY_DB_HOST,
            ConfigOptions::INPUT_KEY_DB_NAME,
            ConfigOptions::INPUT_KEY_DB_USER
        ];

        foreach ($required as $key) {
            $connection[ConfigGenerator::$paramMap[$key]] = $data[$key];
        }

        $connection[self::$paramMap[ConfigOptions::INPUT_KEY_DB_PASS]] =
            isset($data[ConfigOptions::INPUT_KEY_DB_PASS]) ? $data[ConfigOptions::INPUT_KEY_DB_PASS] : '';
        $connection[self::$paramMap[ConfigOptions::INPUT_KEY_DB_MODEL]] =
            isset($data[ConfigOptions::INPUT_KEY_DB_MODEL]) ? $data[ConfigOptions::INPUT_KEY_DB_MODEL] : 'mysql4';
        $connection[self::$paramMap[ConfigOptions::INPUT_KEY_DB_INIT_STATEMENTS]] =
            isset($data[ConfigOptions::INPUT_KEY_DB_INIT_STATEMENTS]) ?
                $data[ConfigOptions::INPUT_KEY_DB_INIT_STATEMENTS] : 'SET NAMES utf8;';
        $connection[self::$paramMap[ConfigOptions::INPUT_KEY_ACTIVE]] = '1';
        $prefixKey = isset($data[ConfigOptions::INPUT_KEY_DB_PREFIX]) ? $data[ConfigOptions::INPUT_KEY_DB_PREFIX] : '';
        $dbData = [
            self::$paramMap[ConfigOptions::INPUT_KEY_DB_PREFIX] => $prefixKey,
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
        $resourceData[self::$paramMap[ConfigOptions::INPUT_KEY_RESOURCE]] =
            ['default_setup' => ['connection' => 'default']];
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'resource', $resourceData);
    }
}
