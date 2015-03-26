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
        ConfigOptionsList::INPUT_KEY_DB_HOST => ConfigOptionsList::KEY_HOST,
        ConfigOptionsList::INPUT_KEY_DB_NAME => ConfigOptionsList::KEY_NAME,
        ConfigOptionsList::INPUT_KEY_DB_USER => ConfigOptionsList::KEY_USER,
        ConfigOptionsList::INPUT_KEY_DB_PASS => ConfigOptionsList::KEY_PASS,
        ConfigOptionsList::INPUT_KEY_DB_PREFIX => ConfigOptionsList::KEY_PREFIX,
        ConfigOptionsList::INPUT_KEY_DB_MODEL => ConfigOptionsList::KEY_MODEL,
        ConfigOptionsList::INPUT_KEY_DB_INIT_STATEMENTS => ConfigOptionsList::KEY_INIT_STATEMENTS,
        ConfigOptionsList::INPUT_KEY_ACTIVE => ConfigOptionsList::KEY_ACTIVE,
        ConfigOptionsList::INPUT_KEY_CRYPT_KEY => ConfigOptionsList::KEY_ENCRYPTION_KEY,
        ConfigOptionsList::INPUT_KEY_SESSION_SAVE => ConfigOptionsList::KEY_SAVE,
        ConfigOptionsList::INPUT_KEY_RESOURCE => ConfigOptionsList::KEY_RESOURCE,
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
        $installConfig = [];
        if (!$this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_INSTALL_DATE)) {
            $installConfig = [ConfigOptionsList::INPUT_KEY_DATE => date('r')];
        }
        return new ConfigData(ConfigFilePool::APP_CONFIG, 'install', $installConfig);
    }

    /**
     * Creates encryption key config data
     * @param array $data
     * @return ConfigData
     */
    public function createCryptConfig(array $data)
    {
        $currentKey = $this->deploymentConfig->get(ConfigOptionsList::CONFIG_PATH_CRYPT_KEY);

        $cryptData = [];
        if (isset($data[ConfigOptionsList::INPUT_KEY_CRYPT_KEY])) {
            if ($currentKey) {
                $key = $currentKey . "\n" . $data[ConfigOptionsList::INPUT_KEY_CRYPT_KEY];
            } else {
                $key = $data[ConfigOptionsList::INPUT_KEY_CRYPT_KEY];
            }

            $cryptData[self::$paramMap[ConfigOptionsList::INPUT_KEY_CRYPT_KEY]] = $key;
        } else {
            if (!$currentKey) {
                $cryptData[self::$paramMap[ConfigOptionsList::INPUT_KEY_CRYPT_KEY]] =
                    md5($this->random->getRandomString(10));
            }
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
        $resourceData = ['default_setup' => ['connection' => 'default']];
        return new ConfigData(ConfigFilePool::APP_CONFIG, ConfigOptionsList::INPUT_KEY_RESOURCE, $resourceData);
    }
}
