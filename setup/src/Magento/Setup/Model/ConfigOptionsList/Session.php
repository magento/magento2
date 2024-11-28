<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;

/**
 * Deployment configuration options needed to configure session storage
 */
class Session implements ConfigOptionsListInterface
{
    public const INPUT_KEY_SESSION_REDIS_HOST = 'session-save-redis-host';
    public const INPUT_KEY_SESSION_REDIS_PORT = 'session-save-redis-port';
    public const INPUT_KEY_SESSION_REDIS_PASSWORD = 'session-save-redis-password';
    public const INPUT_KEY_SESSION_REDIS_TIMEOUT = 'session-save-redis-timeout';
    public const INPUT_KEY_SESSION_REDIS_RETRIES = 'session-save-redis-retries';
    public const INPUT_KEY_SESSION_REDIS_PERSISTENT_IDENTIFIER = 'session-save-redis-persistent-id';
    public const INPUT_KEY_SESSION_REDIS_DATABASE = 'session-save-redis-db';
    public const INPUT_KEY_SESSION_REDIS_COMPRESSION_THRESHOLD = 'session-save-redis-compression-threshold';
    public const INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY = 'session-save-redis-compression-lib';
    public const INPUT_KEY_SESSION_REDIS_LOG_LEVEL = 'session-save-redis-log-level';
    public const INPUT_KEY_SESSION_REDIS_MAX_CONCURRENCY = 'session-save-redis-max-concurrency';
    public const INPUT_KEY_SESSION_REDIS_BREAK_AFTER_FRONTEND = 'session-save-redis-break-after-frontend';
    public const INPUT_KEY_SESSION_REDIS_BREAK_AFTER_ADMINHTML = 'session-save-redis-break-after-adminhtml';
    public const INPUT_KEY_SESSION_REDIS_FIRST_LIFETIME = 'session-save-redis-first-lifetime';
    public const INPUT_KEY_SESSION_REDIS_BOT_FIRST_LIFETIME = 'session-save-redis-bot-first-lifetime';
    public const INPUT_KEY_SESSION_REDIS_BOT_LIFETIME = 'session-save-redis-bot-lifetime';
    public const INPUT_KEY_SESSION_REDIS_DISABLE_LOCKING = 'session-save-redis-disable-locking';
    public const INPUT_KEY_SESSION_REDIS_MIN_LIFETIME = 'session-save-redis-min-lifetime';
    public const INPUT_KEY_SESSION_REDIS_MAX_LIFETIME = 'session-save-redis-max-lifetime';
    public const INPUT_KEY_SESSION_REDIS_SENTINEL_SERVERS = 'session-save-redis-sentinel-servers';
    public const INPUT_KEY_SESSION_REDIS_SENTINEL_MASTER = 'session-save-redis-sentinel-master';
    public const INPUT_KEY_SESSION_REDIS_SENTINEL_VERIFY_MASTER = 'session-save-redis-sentinel-verify-master';
    public const INPUT_KEY_SESSION_REDIS_SENTINEL_CONNECT_RETRIES = 'session-save-redis-sentinel-connect-retries';

    public const CONFIG_PATH_SESSION_REDIS = 'session/redis';
    public const CONFIG_PATH_SESSION_REDIS_HOST = 'session/redis/host';
    public const CONFIG_PATH_SESSION_REDIS_PORT = 'session/redis/port';
    public const CONFIG_PATH_SESSION_REDIS_PASSWORD = 'session/redis/password';
    public const CONFIG_PATH_SESSION_REDIS_TIMEOUT = 'session/redis/timeout';
    public const CONFIG_PATH_SESSION_REDIS_RETRIES = 'session/redis/retries';
    public const CONFIG_PATH_SESSION_REDIS_PERSISTENT_IDENTIFIER = 'session/redis/persistent_identifier';
    public const CONFIG_PATH_SESSION_REDIS_DATABASE = 'session/redis/database';
    public const CONFIG_PATH_SESSION_REDIS_COMPRESSION_THRESHOLD = 'session/redis/compression_threshold';
    public const CONFIG_PATH_SESSION_REDIS_COMPRESSION_LIBRARY = 'session/redis/compression_library';
    public const CONFIG_PATH_SESSION_REDIS_LOG_LEVEL = 'session/redis/log_level';
    public const CONFIG_PATH_SESSION_REDIS_MAX_CONCURRENCY = 'session/redis/max_concurrency';
    public const CONFIG_PATH_SESSION_REDIS_BREAK_AFTER_FRONTEND = 'session/redis/break_after_frontend';
    public const CONFIG_PATH_SESSION_REDIS_BREAK_AFTER_ADMINHTML = 'session/redis/break_after_adminhtml';
    public const CONFIG_PATH_SESSION_REDIS_FIRST_LIFETIME = 'session/redis/first_lifetime';
    public const CONFIG_PATH_SESSION_REDIS_BOT_FIRST_LIFETIME = 'session/redis/bot_first_lifetime';
    public const CONFIG_PATH_SESSION_REDIS_BOT_LIFETIME = 'session/redis/bot_lifetime';
    public const CONFIG_PATH_SESSION_REDIS_DISABLE_LOCKING = 'session/redis/disable_locking';
    public const CONFIG_PATH_SESSION_REDIS_MIN_LIFETIME = 'session/redis/min_lifetime';
    public const CONFIG_PATH_SESSION_REDIS_MAX_LIFETIME = 'session/redis/max_lifetime';
    public const CONFIG_PATH_SESSION_REDIS_SENTINEL_SERVERS = 'session/redis/sentinel_servers';
    public const CONFIG_PATH_SESSION_REDIS_SENTINEL_MASTER = 'session/redis/sentinel_master';
    public const CONFIG_PATH_SESSION_REDIS_SENTINEL_VERIFY_MASTER = 'session/redis/sentinel_verify_master';
    public const CONFIG_PATH_SESSION_REDIS_SENTINEL_CONNECT_RETRIES = 'session/redis/sentinel_connect_retries';

    /**
     * @var array
     */
    private $defaultConfigValues = [
        ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE => ConfigOptionsListConstants::SESSION_SAVE_FILES,
        self::INPUT_KEY_SESSION_REDIS_HOST => '127.0.0.1',
        self::INPUT_KEY_SESSION_REDIS_PORT => '6379',
        self::INPUT_KEY_SESSION_REDIS_PASSWORD => '',
        self::INPUT_KEY_SESSION_REDIS_TIMEOUT => '2.5',
        self::INPUT_KEY_SESSION_REDIS_RETRIES => '0',
        self::INPUT_KEY_SESSION_REDIS_PERSISTENT_IDENTIFIER => '',
        self::INPUT_KEY_SESSION_REDIS_DATABASE => '2',
        self::INPUT_KEY_SESSION_REDIS_COMPRESSION_THRESHOLD => '2048',
        self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY => 'gzip',
        self::INPUT_KEY_SESSION_REDIS_LOG_LEVEL => '1',
        self::INPUT_KEY_SESSION_REDIS_MAX_CONCURRENCY => '6',
        self::INPUT_KEY_SESSION_REDIS_BREAK_AFTER_FRONTEND => '5',
        self::INPUT_KEY_SESSION_REDIS_BREAK_AFTER_ADMINHTML => '30',
        self::INPUT_KEY_SESSION_REDIS_FIRST_LIFETIME => '600',
        self::INPUT_KEY_SESSION_REDIS_BOT_FIRST_LIFETIME => '60',
        self::INPUT_KEY_SESSION_REDIS_BOT_LIFETIME => '7200',
        self::INPUT_KEY_SESSION_REDIS_DISABLE_LOCKING => '0',
        self::INPUT_KEY_SESSION_REDIS_MIN_LIFETIME => '60',
        self::INPUT_KEY_SESSION_REDIS_MAX_LIFETIME => '2592000',
        self::INPUT_KEY_SESSION_REDIS_SENTINEL_VERIFY_MASTER => '0',
        self::INPUT_KEY_SESSION_REDIS_SENTINEL_CONNECT_RETRIES => '5',
    ];

    /**
     * @var array
     */
    private $validSaveHandlers = [
        ConfigOptionsListConstants::SESSION_SAVE_FILES,
        ConfigOptionsListConstants::SESSION_SAVE_DB,
        ConfigOptionsListConstants::SESSION_SAVE_REDIS
    ];

    /**
     * @var array
     */
    private $validCompressionLibraries = ['gzip', 'lzf', 'lz4', 'snappy'];

    /**
     * Associates input keys with config paths for Redis settings
     *
     * @var array
     */
    private $redisInputKeyToConfigPathMap = [
        self::INPUT_KEY_SESSION_REDIS_HOST => self::CONFIG_PATH_SESSION_REDIS_HOST,
        self::INPUT_KEY_SESSION_REDIS_PORT => self::CONFIG_PATH_SESSION_REDIS_PORT,
        self::INPUT_KEY_SESSION_REDIS_PASSWORD => self::CONFIG_PATH_SESSION_REDIS_PASSWORD,
        self::INPUT_KEY_SESSION_REDIS_TIMEOUT => self::CONFIG_PATH_SESSION_REDIS_TIMEOUT,
        self::INPUT_KEY_SESSION_REDIS_RETRIES => self::CONFIG_PATH_SESSION_REDIS_RETRIES,
        self::INPUT_KEY_SESSION_REDIS_PERSISTENT_IDENTIFIER => self::CONFIG_PATH_SESSION_REDIS_PERSISTENT_IDENTIFIER,
        self::INPUT_KEY_SESSION_REDIS_DATABASE => self::CONFIG_PATH_SESSION_REDIS_DATABASE,
        self::INPUT_KEY_SESSION_REDIS_COMPRESSION_THRESHOLD => self::CONFIG_PATH_SESSION_REDIS_COMPRESSION_THRESHOLD,
        self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY => self::CONFIG_PATH_SESSION_REDIS_COMPRESSION_LIBRARY,
        self::INPUT_KEY_SESSION_REDIS_LOG_LEVEL => self::CONFIG_PATH_SESSION_REDIS_LOG_LEVEL,
        self::INPUT_KEY_SESSION_REDIS_MAX_CONCURRENCY => self::CONFIG_PATH_SESSION_REDIS_MAX_CONCURRENCY,
        self::INPUT_KEY_SESSION_REDIS_BREAK_AFTER_FRONTEND => self::CONFIG_PATH_SESSION_REDIS_BREAK_AFTER_FRONTEND,
        self::INPUT_KEY_SESSION_REDIS_BREAK_AFTER_ADMINHTML => self::CONFIG_PATH_SESSION_REDIS_BREAK_AFTER_ADMINHTML,
        self::INPUT_KEY_SESSION_REDIS_FIRST_LIFETIME => self::CONFIG_PATH_SESSION_REDIS_FIRST_LIFETIME,
        self::INPUT_KEY_SESSION_REDIS_BOT_FIRST_LIFETIME => self::CONFIG_PATH_SESSION_REDIS_BOT_FIRST_LIFETIME,
        self::INPUT_KEY_SESSION_REDIS_BOT_LIFETIME => self::CONFIG_PATH_SESSION_REDIS_BOT_LIFETIME,
        self::INPUT_KEY_SESSION_REDIS_DISABLE_LOCKING => self::CONFIG_PATH_SESSION_REDIS_DISABLE_LOCKING,
        self::INPUT_KEY_SESSION_REDIS_MIN_LIFETIME => self::CONFIG_PATH_SESSION_REDIS_MIN_LIFETIME,
        self::INPUT_KEY_SESSION_REDIS_MAX_LIFETIME => self::CONFIG_PATH_SESSION_REDIS_MAX_LIFETIME,
        self::INPUT_KEY_SESSION_REDIS_SENTINEL_MASTER => self::CONFIG_PATH_SESSION_REDIS_SENTINEL_MASTER,
        self::INPUT_KEY_SESSION_REDIS_SENTINEL_SERVERS => self::CONFIG_PATH_SESSION_REDIS_SENTINEL_SERVERS,
        self::INPUT_KEY_SESSION_REDIS_SENTINEL_CONNECT_RETRIES =>
        self::CONFIG_PATH_SESSION_REDIS_SENTINEL_CONNECT_RETRIES,
        self::INPUT_KEY_SESSION_REDIS_SENTINEL_VERIFY_MASTER => self::CONFIG_PATH_SESSION_REDIS_SENTINEL_VERIFY_MASTER,
    ];

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                $this->validSaveHandlers,
                ConfigOptionsListConstants::CONFIG_PATH_SESSION_SAVE,
                'Session save handler',
                $this->getDefaultConfigValue(ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE)
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_HOST,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_HOST,
                'Fully qualified host name, IP address, or absolute path if using UNIX sockets'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_PORT,
                'Redis server listen port'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_PASSWORD,
                'Redis server password'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_TIMEOUT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_TIMEOUT,
                'Connection timeout, in seconds'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_RETRIES,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_RETRIES,
                'Redis connection retries.'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_PERSISTENT_IDENTIFIER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_PERSISTENT_IDENTIFIER,
                'Unique string to enable persistent connections'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_DATABASE,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_DATABASE,
                'Redis database number'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_COMPRESSION_THRESHOLD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_COMPRESSION_THRESHOLD,
                'Redis compression threshold'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_COMPRESSION_LIBRARY,
                $this->getCompressionLibDescription()
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_LOG_LEVEL,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_LOG_LEVEL,
                'Redis log level. Values: 0 (least verbose) to 7 (most verbose)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_MAX_CONCURRENCY,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_MAX_CONCURRENCY,
                'Maximum number of processes that can wait for a lock on one session'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_BREAK_AFTER_FRONTEND,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_BREAK_AFTER_FRONTEND,
                'Number of seconds to wait before trying to break a lock for frontend session'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_BREAK_AFTER_ADMINHTML,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_BREAK_AFTER_ADMINHTML,
                'Number of seconds to wait before trying to break a lock for Admin session'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_FIRST_LIFETIME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_FIRST_LIFETIME,
                'Lifetime, in seconds, of session for non-bots on the first write (use 0 to disable)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_BOT_FIRST_LIFETIME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_BOT_FIRST_LIFETIME,
                'Lifetime, in seconds, of session for bots on the first write (use 0 to disable)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_BOT_LIFETIME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_BOT_LIFETIME,
                'Lifetime of session for bots on subsequent writes (use 0 to disable)'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_DISABLE_LOCKING,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_DISABLE_LOCKING,
                'Redis disable locking. Values: false (default), true'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_MIN_LIFETIME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_MIN_LIFETIME,
                'Redis min session lifetime, in seconds'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_MAX_LIFETIME,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_MAX_LIFETIME,
                'Redis max session lifetime, in seconds'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_SENTINEL_MASTER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_SENTINEL_MASTER,
                'Redis Sentinel master'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_SENTINEL_SERVERS,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::INPUT_KEY_SESSION_REDIS_SENTINEL_SERVERS,
                'Redis Sentinel servers, comma separated'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_SENTINEL_VERIFY_MASTER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_SENTINEL_VERIFY_MASTER,
                'Redis Sentinel verify master. Values: false (default), true'
            ),
            new TextConfigOption(
                self::INPUT_KEY_SESSION_REDIS_SENTINEL_CONNECT_RETRIES,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                self::CONFIG_PATH_SESSION_REDIS_SENTINEL_CONNECT_RETRIES,
                'Redis Sentinel connect retries.'
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        //Set session-save option
        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE])) {
            $configData->set(
                ConfigOptionsListConstants::CONFIG_PATH_SESSION_SAVE,
                $options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]
            );

            if ($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]
                == ConfigOptionsListConstants::SESSION_SAVE_REDIS
            ) {
                $this->setDefaultRedisConfig($deploymentConfig, $configData);
            }
        }

        //Set all Redis options that are specified
        foreach ($this->redisInputKeyToConfigPathMap as $inputKey => $configPath) {
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
        if (isset($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE])
            && !in_array($options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE], $this->validSaveHandlers)
        ) {
            $errors[] = "Invalid session handler '{$options[ConfigOptionsListConstants::INPUT_KEY_SESSION_SAVE]}'";
        }

        if (isset($options[self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY])
            && !in_array($options[self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY], $this->validCompressionLibraries)
        ) {
            $errors[] = "Invalid Redis compression library "
                . "'{$options[self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY]}'";
        }

        if (isset($options[self::INPUT_KEY_SESSION_REDIS_LOG_LEVEL])) {
            $level = $options[self::INPUT_KEY_SESSION_REDIS_LOG_LEVEL];
            if (($level < 0) || ($level > 7)) {
                $errors[] = "Invalid Redis log level '{$level}'. Valid range is 0-7, inclusive.";
            }
        }

        return $errors;
    }

    /**
     * Set the session Redis config based on defaults
     *
     * @param DeploymentConfig $deploymentConfig
     * @param ConfigData $configData
     * @return ConfigData
     */
    private function setDefaultRedisConfig(DeploymentConfig $deploymentConfig, ConfigData $configData)
    {
        foreach ($this->redisInputKeyToConfigPathMap as $inputKey => $configPath) {
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

    /**
     * Format the description for compression lib option
     *
     * @return string
     */
    private function getCompressionLibDescription()
    {
        $default = $this->getDefaultConfigValue(self::INPUT_KEY_SESSION_REDIS_COMPRESSION_LIBRARY);
        $otherLibs = array_diff($this->validCompressionLibraries, [$default]);

        return 'Redis compression library. Values: ' . $default . ' (default), ' . implode(', ', $otherLibs);
    }
}
