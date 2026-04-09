<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
    public const CONFIG_VALUE_CACHE_REDIS = 'redis';

    public const INPUT_VALUE_CACHE_VALKEY = 'valkey';
    public const CONFIG_VALUE_CACHE_VALKEY = 'valkey';

    public const INPUT_KEY_CACHE_BACKEND = 'cache-backend';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_SERVER = 'cache-backend-redis-server';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE = 'cache-backend-redis-db';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_PORT = 'cache-backend-redis-port';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD = 'cache-backend-redis-password';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESS_DATA = 'cache-backend-redis-compress-data';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_COMPRESSION_LIB = 'cache-backend-redis-compression-lib';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_SERIALIZER = 'cache-backend-redis-serializer';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_LUA_KEY = 'cache-backend-redis-lua-key';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA = 'cache-backend-redis-use-lua';
    public const INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA_ON_GC = 'cache-backend-redis-use-lua-on-gc';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_SERVER = 'cache-backend-valkey-server';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_DATABASE = 'cache-backend-valkey-db';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_PORT = 'cache-backend-valkey-port';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_PASSWORD = 'cache-backend-valkey-password';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESS_DATA = 'cache-backend-valkey-compress-data';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESSION_LIB = 'cache-backend-valkey-compression-lib';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_SERIALIZER = 'cache-backend-valkey-serializer';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_LUA_KEY = 'cache-backend-valkey-lua-key';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA = 'cache-backend-valkey-use-lua';
    public const INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA_ON_GC = 'cache-backend-valkey-use-lua-on-gc';
    public const INPUT_KEY_CACHE_ID_PREFIX = 'cache-id-prefix';
    public const INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION = 'allow-parallel-generation';

    public const CONFIG_PATH_CACHE_BACKEND = 'cache/frontend/default/backend';
    public const CONFIG_PATH_CACHE_BACKEND_SERVER = 'cache/frontend/default/backend_options/server';
    public const CONFIG_PATH_CACHE_BACKEND_DATABASE = 'cache/frontend/default/backend_options/database';
    public const CONFIG_PATH_CACHE_BACKEND_PORT = 'cache/frontend/default/backend_options/port';
    public const CONFIG_PATH_CACHE_BACKEND_PASSWORD = 'cache/frontend/default/backend_options/password';
    public const CONFIG_PATH_CACHE_BACKEND_COMPRESS_DATA = 'cache/frontend/default/backend_options/compress_data';
    public const CONFIG_PATH_CACHE_BACKEND_COMPRESSION_LIB = 'cache/frontend/default/backend_options/compression_lib';
    public const CONFIG_PATH_CACHE_BACKEND_SERIALIZER = 'cache/frontend/default/backend_options/serializer';
    public const CONFIG_PATH_CACHE_BACKEND_LUA_KEY = 'cache/frontend/default/backend_options/_useLua';
    public const CONFIG_PATH_CACHE_BACKEND_USE_LUA = 'cache/frontend/default/backend_options/use_lua';
    public const CONFIG_PATH_CACHE_BACKEND_USE_LUA_ON_GC = 'cache/frontend/default/backend_options/use_lua_on_gc';
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
        self::INPUT_KEY_CACHE_BACKEND_REDIS_SERIALIZER => 'igbinary',
        self::INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION => 'false',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA => '0',
        self::INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA_ON_GC => '1',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERVER => '127.0.0.1',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_DATABASE => '0',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_PORT => '6379',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_PASSWORD => '',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESS_DATA => '1',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESSION_LIB => '',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERIALIZER => 'igbinary',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA => '0',
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA_ON_GC => '1',
    ];

    /**
     * @var array
     */
    private $validBackendCacheOptions = [
        self::INPUT_VALUE_CACHE_REDIS,
        self::INPUT_VALUE_CACHE_VALKEY
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
        self::INPUT_KEY_CACHE_BACKEND_REDIS_SERIALIZER => self::CONFIG_PATH_CACHE_BACKEND_SERIALIZER,
        self::INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION => self::CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA => self::CONFIG_PATH_CACHE_BACKEND_USE_LUA,
        self::INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA_ON_GC=> self::CONFIG_PATH_CACHE_BACKEND_USE_LUA_ON_GC,
    ];

    /**
     * @var array
     */
    private $inputKeyToValkeyConfigPathMap = [
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERVER => self::CONFIG_PATH_CACHE_BACKEND_SERVER,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_DATABASE => self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_PORT => self::CONFIG_PATH_CACHE_BACKEND_PORT,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_PASSWORD => self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESS_DATA => self::CONFIG_PATH_CACHE_BACKEND_COMPRESS_DATA,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESSION_LIB => self::CONFIG_PATH_CACHE_BACKEND_COMPRESSION_LIB,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERIALIZER => self::CONFIG_PATH_CACHE_BACKEND_SERIALIZER,
        self::INPUT_KEY_CACHE_ALLOW_PARALLEL_CACHE_GENERATION => self::CONFIG_PATH_ALLOW_PARALLEL_CACHE_GENERATION,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA => self::CONFIG_PATH_CACHE_BACKEND_USE_LUA,
        self::INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA_ON_GC=> self::CONFIG_PATH_CACHE_BACKEND_USE_LUA_ON_GC,
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
        return array_merge(
            $this->getRedisOptions(),
            $this->getValkeyOptions(),
            $this->getGenericOptions()
        );
    }

    /**
     * Returns the list of Redis-specific configuration options.
     *
     * @return array
     */
    private function getRedisOptions(): array
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
                self::INPUT_KEY_CACHE_BACKEND_REDIS_SERIALIZER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_SERIALIZER,
                'Serializer to use (igbinary is 70% faster, 58% smaller than PHP serialize)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_USE_LUA,
                'Set to 1 to enable lua (default is 0, disabled)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_REDIS_USE_LUA_ON_GC,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_USE_LUA_ON_GC,
                'Set to 0 to disable lua on garbage collection (default is 1, enabled)'
            )
        ];
    }

    /**
     * Returns the list of Valkey-specific configuration options.
     *
     * @return array
     */
    private function getValkeyOptions(): array
    {
        return [
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERVER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_SERVER,
                'Valkey server'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_DATABASE,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
                'Database number for the cache'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_PORT,
                'Valkey server listen port'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
                'Valkey server password'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESS_DATA,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_COMPRESS_DATA,
                'Set to 0 to disable compression (default is 1, enabled)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_COMPRESSION_LIB,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_COMPRESSION_LIB,
                'Compression lib to use [snappy,lzf,l4z,zstd,gzip] (leave blank to determine automatically)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERIALIZER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_SERIALIZER,
                'Serializer to use (igbinary is 70% faster, 58% smaller than PHP serialize)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_USE_LUA,
                'Set to 1 to enable lua (default is 0, disabled)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_CACHE_BACKEND_VALKEY_USE_LUA_ON_GC,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_CACHE_BACKEND_USE_LUA_ON_GC,
                'Set to 0 to disable lua on garbage collection (default is 1, enabled)'
            )
        ];
    }

    /**
     * Returns generic configuration options applicable to all backends.
     *
     * @return array
     */
    private function getGenericOptions(): array
    {
        return [
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
            )
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
            } elseif ($options[self::INPUT_KEY_CACHE_BACKEND] == self::INPUT_VALUE_CACHE_VALKEY) {
                $configData->set(self::CONFIG_PATH_CACHE_BACKEND, self::CONFIG_VALUE_CACHE_VALKEY);
                $this->setDefaultValkeyConfig($deploymentConfig, $configData);
            } else {
                $configData->set(self::CONFIG_PATH_CACHE_BACKEND, $options[self::INPUT_KEY_CACHE_BACKEND]);
            }
        } else {
            // If no backend specified, set igbinary as default serializer for file backend
            $this->setDefaultFileConfig($deploymentConfig, $configData);
        }

        $this->applyCacheBackendConfig($options, $configData);

        return $configData;
    }

    /**
     * Applies cache backend configuration to the config data based on the selected Redis or Valkey backend.
     *
     * @param array $options
     * @param ConfigData $configData
     *
     * @return void
     */
    private function applyCacheBackendConfig(array $options, ConfigData $configData): void
    {
        if (isset($options[self::INPUT_KEY_CACHE_BACKEND])) {
            $map = $options[self::INPUT_KEY_CACHE_BACKEND] === self::INPUT_VALUE_CACHE_VALKEY
                ? $this->inputKeyToValkeyConfigPathMap
                : $this->inputKeyToConfigPathMap;

            foreach ($map as $inputKey => $configPath) {
                if (isset($options[$inputKey])) {
                    $configData->set($configPath, $options[$inputKey]);
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $errors = [];

        $selectedBackend = $options[self::INPUT_KEY_CACHE_BACKEND] ?? null;
        $currentBackend = $deploymentConfig->get(Cache::CONFIG_PATH_CACHE_BACKEND);

        // Validate if selected backend is Redis or Valkey
        if (in_array($selectedBackend, [self::INPUT_VALUE_CACHE_REDIS, self::INPUT_VALUE_CACHE_VALKEY], true)) {
            if (!$this->validateRedisConfig($options, $deploymentConfig)) {
                $errors[] = "Invalid {$selectedBackend} configuration. Could not connect to {$selectedBackend} server.";
            }
        }

        // Validate if switching away from Redis/Valkey
        if (!$selectedBackend &&
            in_array($currentBackend, [self::CONFIG_VALUE_CACHE_REDIS, self::CONFIG_VALUE_CACHE_VALKEY], true)) {
            if (!$this->validateRedisConfig($options, $deploymentConfig)) {
                $errors[] = "Invalid {$currentBackend} configuration. Could not connect to {$currentBackend} server.";
            }
        }

        // Validate backend value is in allowed list
        if ($selectedBackend && !in_array($selectedBackend, $this->validBackendCacheOptions, true)) {
            $errors[] = "Invalid cache handler '{$selectedBackend}'";
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
        if ($options[self::INPUT_KEY_CACHE_BACKEND] == self::INPUT_VALUE_CACHE_VALKEY
            || $options[PageCache::INPUT_KEY_PAGE_CACHE_BACKEND] == PageCache::INPUT_VALUE_PAGE_CACHE_VALKEY) {
            $config['host'] = $options[self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERVER] ??
                $deploymentConfig->get(
                    self::CONFIG_PATH_CACHE_BACKEND_SERVER,
                    $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_VALKEY_SERVER)
                );

            $config['port'] = $options[self::INPUT_KEY_CACHE_BACKEND_VALKEY_PORT] ??
                $deploymentConfig->get(
                    self::CONFIG_PATH_CACHE_BACKEND_PORT,
                    $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_VALKEY_PORT)
                );

            $config['db'] = $options[self::INPUT_KEY_CACHE_BACKEND_VALKEY_DATABASE] ??
                $deploymentConfig->get(
                    self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
                    $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_VALKEY_DATABASE)
                );

            $config['password'] = $options[self::INPUT_KEY_CACHE_BACKEND_VALKEY_PASSWORD] ??
                $deploymentConfig->get(
                    self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
                    $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_VALKEY_PASSWORD)
                );
        } else {
            $config['host'] = $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER] ??
            $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_SERVER,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_SERVER)
            );

            $config['port'] = $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT] ??
            $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_PORT,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_PORT)
            );

            $config['db'] = $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE] ??
            $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_DATABASE,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_DATABASE)
            );

            $config['password'] = $options[self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD] ??
            $deploymentConfig->get(
                self::CONFIG_PATH_CACHE_BACKEND_PASSWORD,
                $this->getDefaultConfigValue(self::INPUT_KEY_CACHE_BACKEND_REDIS_PASSWORD)
            );
        }
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
     * Set default values for the Valkey configuration.
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigData $configData
     * @return ConfigData
     */
    private function setDefaultValkeyConfig(DeploymentConfig $deploymentConfig, ConfigData $configData)
    {
        foreach ($this->inputKeyToValkeyConfigPathMap as $inputKey => $configPath) {
            $configData->set($configPath, $deploymentConfig->get($configPath, $this->getDefaultConfigValue($inputKey)));
        }

        return $configData;
    }

    /**
     * Set default configuration for file backend (enables igbinary by default)
     *
     * When no backend is specified, Magento defaults to file cache.
     * This method ensures igbinary serializer is enabled for optimal performance.
     *
     * Benefits of igbinary for file cache:
     * - 70% faster serialization/deserialization
     * - 58% smaller cache files
     * - Works automatically with FilesystemAdapter
     * - Graceful fallback if extension not available
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigData $configData
     * @return ConfigData
     */
    private function setDefaultFileConfig(DeploymentConfig $deploymentConfig, ConfigData $configData)
    {
        // Set igbinary as default serializer for file backend if not already configured
        if (!$deploymentConfig->get(self::CONFIG_PATH_CACHE_BACKEND_SERIALIZER)) {
            $configData->set(self::CONFIG_PATH_CACHE_BACKEND_SERIALIZER, 'igbinary');
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
