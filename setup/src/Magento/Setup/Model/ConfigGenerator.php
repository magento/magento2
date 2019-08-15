<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\Data\ConfigDataFactory;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\State;
use Magento\Framework\Math\Random;

/**
 * Creates deployment config data based on user input array
 *
 * This class introduced to break down {@see Magento\Setup\Model\ConfigOptionsList::createConfig}
 */
class ConfigGenerator
{
    /**
     * Maps configuration parameters to array keys in deployment config file
     *
     * @var array
     */
    private static $paramMap = [
        ConfigOptionsListConstants::INPUT_KEY_DB_HOST            => ConfigOptionsListConstants::KEY_HOST,
        ConfigOptionsListConstants::INPUT_KEY_DB_NAME            => ConfigOptionsListConstants::KEY_NAME,
        ConfigOptionsListConstants::INPUT_KEY_DB_USER            => ConfigOptionsListConstants::KEY_USER,
        ConfigOptionsListConstants::INPUT_KEY_DB_PASSWORD        => ConfigOptionsListConstants::KEY_PASSWORD,
        ConfigOptionsListConstants::INPUT_KEY_DB_PREFIX          => ConfigOptionsListConstants::KEY_PREFIX,
        ConfigOptionsListConstants::INPUT_KEY_DB_MODEL           => ConfigOptionsListConstants::KEY_MODEL,
        ConfigOptionsListConstants::INPUT_KEY_DB_ENGINE          => ConfigOptionsListConstants::KEY_ENGINE,
        ConfigOptionsListConstants::INPUT_KEY_DB_INIT_STATEMENTS => ConfigOptionsListConstants::KEY_INIT_STATEMENTS,
        ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY     => ConfigOptionsListConstants::KEY_ENCRYPTION_KEY,
        ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE       => ConfigOptionsListConstants::KEY_SAVE,
        ConfigOptionsListConstants::INPUT_KEY_RESOURCE           => ConfigOptionsListConstants::KEY_RESOURCE,
    ];

    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @var Random
     * @deprecated 100.2.0
     */
    protected $random;

    /**
     * @var ConfigDataFactory
     */
    private $configDataFactory;

    /**
     * @var CryptKeyGeneratorInterface
     */
    private $cryptKeyGenerator;

    /**
     * Constructor
     *
     * @param Random $random Deprecated since 100.2.0
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigDataFactory|null $configDataFactory
     * @param CryptKeyGeneratorInterface|null $cryptKeyGenerator
     */
    public function __construct(
        Random $random,
        DeploymentConfig $deploymentConfig,
        ConfigDataFactory $configDataFactory = null,
        CryptKeyGeneratorInterface $cryptKeyGenerator = null
    ) {
        $this->random = $random;
        $this->deploymentConfig = $deploymentConfig;
        $this->configDataFactory = $configDataFactory ?? ObjectManager::getInstance()->get(ConfigDataFactory::class);
        $this->cryptKeyGenerator = $cryptKeyGenerator ?? ObjectManager::getInstance()->get(CryptKeyGenerator::class);
    }

    /**
     * Creates encryption key config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createCryptConfig(array $data)
    {
        $currentKey = $this->deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY);

        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

        // Use given key if set, else use current
        $key = $data[ConfigOptionsListConstants::INPUT_KEY_ENCRYPTION_KEY] ?? $currentKey;

        // If there is no key given or currently set, generate a new one
        $key = $key ?? $this->cryptKeyGenerator->generate();

        // Chaining of ".. ?? .." is not used to keep it simpler to understand

        $configData->set(ConfigOptionsListConstants::CONFIG_PATH_CRYPT_KEY, $key);

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
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

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
     * @deprecated 2.2.0
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createDefinitionsConfig(array $data)
    {
        return null;
    }

    /**
     * Creates db config data
     *
     * @param array $data
     * @return ConfigData
     */
    public function createDbConfig(array $data)
    {
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

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

        $dbConnectionPrefix = ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTION_DEFAULT . '/';

        foreach ($optional as $key) {
            if (isset($data[$key])) {
                $configData->set($dbConnectionPrefix . self::$paramMap[$key], $data[$key]);
            }
        }

        $currentStatus = $this->deploymentConfig->get($dbConnectionPrefix . ConfigOptionsListConstants::KEY_ACTIVE);

        if ($currentStatus === null) {
            $configData->set($dbConnectionPrefix . ConfigOptionsListConstants::KEY_ACTIVE, '1');
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
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

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
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

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
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

        if ($this->deploymentConfig->get(State::PARAM_MODE) === null) {
            $configData->set(State::PARAM_MODE, State::MODE_DEFAULT);
        }

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
        $configData = $this->configDataFactory->create(ConfigFilePool::APP_ENV);

        if (isset($data[ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS])) {
            $hosts = explode(',', $data[ConfigOptionsListConstants::INPUT_KEY_CACHE_HOSTS]);

            $hosts = array_map(
                function ($hostData) {
                    $hostDataParts = explode(':', trim($hostData));

                    $tmp = ['host' => $hostDataParts[0]];

                    if (isset($hostDataParts[1])) {
                        $tmp['port'] = $hostDataParts[1];
                    }

                    return $tmp;
                },
                $hosts
            );

            $configData->set(ConfigOptionsListConstants::CONFIG_PATH_CACHE_HOSTS, $hosts);
        }

        $configData->setOverrideWhenSave(true);
        return $configData;
    }
}
