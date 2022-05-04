<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\Option\FlagConfigOption;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Validator\RedisConnectionValidator;

/**
 * Deployment configuration options for the default cache
 */
class Cache implements ConfigOptionsListInterface
{
    public const INPUT_VALUE_CACHE_REDIS = 'redis';
    public const CONFIG_VALUE_CACHE_REDIS = \Magento\Framework\Cache\Backend\Redis::class;

    public const INPUT_KEY_CACHE_BACKEND = 'cache-backend';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_SERVER = 'cache-backend-redis-server';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE = 'cache-backend-redis-db';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_PORT = 'cache-backend-redis-port';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD = 'cache-backend-redis-password';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESS_DATA = 'cache-backend-redis-compress-data';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESSION_LIB = 'cache-backend-redis-compression-lib';
    public const INPUT_KEY_CACHE_ID_PREFIX = 'cache-id-prefix';
    public const INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION = 'allow-parallel-generation';

    public const CONFIG_PATH_CACHE_BACKEND = 'cache/frontend/default/backend';
    public const CONFIG_PATH_CACHE_BACKEND_SERVER = 'cache/frontend/default/backend_options/server';
    public const CONFIG_PATH_CACHE_BACKEND_DATABASE = 'cache/frontend/default/backend_options/database';
    public const CONFIG_PATH_CACHE_BACKEND_PORT = 'cache/frontend/default/backend_options/port';
    public const CONFIG_PATH_CACHE_BACKEND_PASSWORD = 'cache/frontend/default/backend_options/password';
    public const CONFIG_PATH_CACHE_BACKEND_COMPRESS_DATA = 'cache/frontend/default/backend_options/compress_data';
    public const CONFIG_PATH_CACHE_BACKEND_COMPRESSION_LIB = 'cache/frontend/default/backend_options/compression_lib';
    public const CONFIG_PATH_CACHE_ID_PREFIX = 'cache/frontend/default/id_prefix';
    public const CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION = 'cache/allow_parallel_generation';

    /**
     * @var array
     */
    private $defaultConfigValues = [
        self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER => '127.0.0.1',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE => '0',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT => '6379',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD => '',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESS_DATA => '1',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESSION_LIB => '',
        self::INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION => 'false',
    ];

    /**
     * @var array
     */
    private $validBackendCacheOptions = [
        self::INPUT_VALUE_CACHE_REDIS
    ];

    /**
     * @var array
     */
    private $inputKeyToConfigPathMap = [
        self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER => self::CONFIG_PATH_CACHE_BACKEND_SERVER,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE => self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT => self::CONFIG_PATH_CACHE_BACKEND_PORT,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD => self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESS_DATA => self::CONFIG_PATH_CACHE_BACKEND_COMPRESS_DATA,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESSION_LIB => self::CONFIG_PATH_CACHE_BACKEND_COMPRESSION_LIB,
        self::INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION => self::CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION,
    ];

    /**
     * @var RedisConnectionValidator
     */
    private $redisValidator;

    /**
     * Construct the Cache ConfigOptionsList
     *
     * @param RedisConnectionValidator $redisValidator
     */
    public function __construct(RedisConnectionValidator $redisValidator)
    {
        $this->redisValidator = $redisValidator;
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_CACHE_BACKEND,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->validBackendCacheOptions,
                self::CONFIG_PATH_CACHE_BACKEND,
                'Default cache handler'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_SERVER,
                'Redis server'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
                'Database number for the cache'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_PORT,
                'Redis server listen port'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
                'Redis server password'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESS_DATA,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_COMPRESS_DATA,
                'Set to 0 to disable compression (default is 1, enabled)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESSION_LIB,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_COMPRESSION_LIB,
                'Compression lib to use [snappy,lzf,l4z,zstd,gzip] (leave blank to determine automatically)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_ID_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_ID_PREFIX,
                'ID prefix for cache keys'
            ),
            new FlagConfigOption(
                self::INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION,
                self::CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION,
                'Allow generate cache in non-blocking way'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);
        if (isset($options[self::INPUT_KEY_CACHE_ID_PREFIX])) {
            $configData->set(self::CONFIG_PATH_CACHE_ID_PREFIX, $options[self::INPUT_KEY_CACHE_ID_PREFIX]);
        } elseif (!$deploymentConfig->get(self::CONFIG_PATH_CACHE_ID_PREFIX)) {
            $configData->set(self::CONFIG_PATH_CACHE_ID_PREFIX, $this->generateCachePrefix());
        }

        if (isset($options[self::INPUT_KEY_CACHE_BACKEND])) {
            if ($options[self::INPUT_KEY_CACHE_BACKEND] == self::INPUT_VALUE_CACHE_REDIS) {
                $configData->set(self::CONFIG_PATH_CACHE_BACKEND, self::CONFIG_VALUE_CACHE_REDIS);
                $this->setDefaultRedisConfig($deploymentConfig, $configData);
            } else {
                $configData->set(self::CONFIG_PATH_CACHE_BACKEND, $options[self::INPUT_KEY_CACHE_BACKEND]);
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
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        $currentCacheBackend = $deploymentConfig->get(Cache::CONFIG_PATH_CACHE_BACKEND);
        if (isset($options[self::INPUT_KEY_CACHE_BACKEND])) {
            if ($options[self::INPUT_KEY_CACHE_BACKEND] == self::INPUT_VALUE_CACHE_REDIS) {
                if (!$this->validateRedisConfig($options, $deploymentConfig)) {
                    $errors[] = 'Invalid Redis configuration. Could not connect to Redis server.';
                }
            }
        } elseif ($currentCacheBackend == self::CONFIG_VALUE_CACHE_REDIS) {
            if (!$this->validateRedisConfig($options, $deploymentConfig)) {
                $errors[] = 'Invalid Redis configuration. Could not connect to Redis server.';
            }
        }

        if (isset($options[self::INPUT_KEY_CACHE_BACKEND])
            && !in_array($options[self::INPUT_KEY_CACHE_BACKEND], $this->validBackendCacheOptions)
        ) {
            $errors[] = "Invalid cache handler '{$options[self::INPUT_KEY_CACHE_BACKEND]}'";
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

        $config['host'] = isset($options[self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER])
            ? $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER]
            : $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_SERVER,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER)
            );

        $config['port'] = isset($options[self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT])
            ? $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT]
            : $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_PORT,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT)
            );

        $config['db'] = isset($options[self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE])
            ? $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE]
            : $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE)
            );

        $config['password'] = isset($options[self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD])
            ? $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD]
            : $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD)
            );

        return $this->redisValidator->isValidConnection($config);
    }

    /**
     * Set default values for the Redis configuration.
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
     * Get default value for input key
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

    /**
     * Generate default cache ID prefix based on installation dir
     *
     * @return string
     */
    private function generateCachePrefix(): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return substr(\hash('sha256', dirname(__DIR__, 6)), 0, 3) . '_';
    }
}
