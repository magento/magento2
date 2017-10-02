<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Validator\RedisConnectionValidator;

/**
 * Deployment configuration options for full page cache
 */
class PageCache implements ConfigOptionsListInterface
{
    const INPUT_VALUE_PAGE_CACHE_REDIS = 'redis';
    const CONFIG_VALUE_PAGE_CACHE_REDIS = 'Cm_Cache_Backend_Redis';

    const INPUT_KEY_PAGE_CACHE_BACKEND = 'page-cache';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER = 'page-cache-redis-server';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE = 'page-cache-redis-db';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT = 'page-cache-redis-port';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_COMPRESS_DATA = 'page-cache-redis-compress-data';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER = 'page-cache-redis-sentinel-master';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY = 'page-cache-redis-sentinel-master-verify';
    const INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES= 'page-cache-redis-sentinel-load-from-slaves';

    const CONFIG_PATH_PAGE_CACHE_BACKEND = 'cache/frontend/page_cache/backend';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_SERVER = 'cache/frontend/page_cache/backend_options/server';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_DATABASE = 'cache/frontend/page_cache/backend_options/database';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_PORT = 'cache/frontend/page_cache/backend_options/port';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_COMPRESS_DATA =
        'cache/frontend/page_cache/backend_options/compress_data';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER =
        'cache/frontend/page_cache/backend_options/sentinel_master';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY =
        'cache/frontend/page_cache/backend_options/sentinel_master_verify';
    const CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES =
        'cache/frontend/page_cache/backend_options/load_from_slaves';

    /**
     * @var array
     */
    private $defaultConfigValues = [
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER => '127.0.0.1',
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE => '1',
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT => '6379',
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_COMPRESS_DATA => '0',
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER => null,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY => null,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES => null,
    ];

    /**
     * @var array
     */
    private $validPageCacheOptions = [
        self::INPUT_VALUE_PAGE_CACHE_REDIS
    ];

    /**
     * @var array
     */
    private $inputKeyToConfigPathMap = [
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER => self::CONFIG_PATH_PAGE_CACHE_BACKEND_SERVER,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE => self::CONFIG_PATH_PAGE_CACHE_BACKEND_DATABASE,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT => self::CONFIG_PATH_PAGE_CACHE_BACKEND_PORT,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_COMPRESS_DATA => self::CONFIG_PATH_PAGE_CACHE_BACKEND_COMPRESS_DATA,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER =>
            self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY =>
            self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY,
        self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES =>
            self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES,
    ];

    /**
     * @var RedisConnectionValidator
     */
    private $redisValidator;

    /**
     * Construct the PageCache ConfigOptionsList
     *
     * @param RedisConnectionValidator $redisValidator
     */
    public function __construct(RedisConnectionValidator $redisValidator)
    {
        $this->redisValidator = $redisValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->validPageCacheOptions,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND,
                'Default cache handler'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_SERVER,
                'Redis server'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_DATABASE,
                'Database number for the cache'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_PORT,
                'Redis server listen port'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_COMPRESS_DATA,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_COMPRESS_DATA,
                'Set to 1 to compress the full page cache (use 0 to disable)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER,
                'Redis sentinel master'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY,
                'Verify connected server is actually master'
            ),
            new TextConfigOption(
                self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_LOAD_FROM_SLAVES,
                'Using the value \'1\' indicates to only load from slaves ' .
                    'and \'2\' to include the master in the random read slave selection'
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        if (isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND])) {
            if ($options[self::INPUT_KEY_PAGE_CACHE_BACKEND] == self::INPUT_VALUE_PAGE_CACHE_REDIS) {
                $configData->set(self::CONFIG_PATH_PAGE_CACHE_BACKEND, self::CONFIG_VALUE_PAGE_CACHE_REDIS);
                $this->setDefaultRedisConfig($deploymentConfig, $configData);
            } else {
                $configData->set(self::CONFIG_PATH_PAGE_CACHE_BACKEND, $options[self::INPUT_KEY_PAGE_CACHE_BACKEND]);
            }
        }

        foreach ($this->inputKeyToConfigPathMap as $inputKey => $configPath) {
            if (isset($options[$inputKey])) {
                $configData->set($configPath, $options[$inputKey]);
            }
        }

        return $configData;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        $currentCacheBackend = $deploymentConfig->get(PageCache::CONFIG_PATH_PAGE_CACHE_BACKEND);
        if (isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND])) {
            if ($options[self::INPUT_KEY_PAGE_CACHE_BACKEND] == self::INPUT_VALUE_PAGE_CACHE_REDIS) {
                if (!$this->validateRedisConfig($options, $deploymentConfig)) {
                    $errors[] = 'Invalid Redis configuration. Could not connect to Redis server.';
                }
            }
        } elseif ($currentCacheBackend == self::CONFIG_VALUE_PAGE_CACHE_REDIS) {
            if (!$this->validateRedisConfig($options, $deploymentConfig)) {
                $errors[] = 'Invalid Redis configuration. Could not connect to Redis server.';
            }
        }

        if (isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND])
            && !in_array($options[self::INPUT_KEY_PAGE_CACHE_BACKEND], $this->validPageCacheOptions)
        ) {
            $errors[] = "Invalid cache handler '{$options[self::INPUT_KEY_PAGE_CACHE_BACKEND]}'";
        }

        return $errors;
    }

    /**
     * Validate that Redis connection succeeds for given configuration
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return bool
     */
    private function validateRedisConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $config = [];

        $config['host'] = isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER])
            ? $options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER]
            : $deploymentConfig->get(
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_SERVER,
                $this->getDefaultConfigValue(self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SERVER)
            );

        $config['port'] = isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT])
            ? $options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT]
            : $deploymentConfig->get(
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_PORT,
                $this->getDefaultConfigValue(self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_PORT)
            );

        $config['db'] = isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE])
            ? $options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE]
            : $deploymentConfig->get(
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_DATABASE,
                $this->getDefaultConfigValue(self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_DATABASE)
            );

        $config['sentinel_master'] = isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER])
            ? $options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER]
            : $deploymentConfig->get(
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER,
                null
            );

        $config['sentinel_master_verify'] =
            isset($options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY])
            ? $options[self::INPUT_KEY_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY]
            : $deploymentConfig->get(
                self::CONFIG_PATH_PAGE_CACHE_BACKEND_REDIS_SENTINEL_MASTER_VERIFY,
                null
            );

        return $this->redisValidator->isValidConnection(array_filter($config));
    }

    /**
     * Set default values for Redis configuration
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigData $configData
     * @return ConfigData
     */
    private function setDefaultRedisConfig(DeploymentConfig $deploymentConfig, ConfigData $configData)
    {
        foreach ($this->inputKeyToConfigPathMap as $inputKey => $configPath) {
            $configData->set($configPath, $deploymentConfig->get($configPath, $this->getDefaultConfigValue($inputKey)));
        }

        return $configData;
    }

    /**
     * Get the default value for input key
     *
     * @param string $inputKey
     * @return string
     */
    private function getDefaultConfigValue($inputKey)
    {
        if (isset($this->defaultConfigValues[$inputKey])) {
            return $this->defaultConfigValues[$inputKey];
        } else {
            return '';
        }
    }
}
