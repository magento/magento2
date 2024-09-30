<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger;
use Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger\RedisClient;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\Data\ConfigData;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Setup\ConfigOptionsListInterface;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\Framework\Setup\Option\TextConfigOption;
use Magento\Setup\Validator\RedisConnectionValidator;

/**
 * Deployment configuration options needed to configure backpressure logger
 */
class BackpressureLogger implements ConfigOptionsListInterface
{
    /**
     *  Input keys for configure backpressure logger
     */
    private const INPUT_KEY_BACKPRESSURE_LOGGER = 'backpressure-logger';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_SERVER = 'backpressure-logger-redis-server';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PORT = 'backpressure-logger-redis-port';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_TIMEOUT = 'backpressure-logger-redis-timeout';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PERSISTENT = 'backpressure-logger-redis-persistent';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_DB = 'backpressure-logger-redis-db';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PASSWORD = 'backpressure-logger-redis-password';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_USER = 'backpressure-logger-redis-user';
    private const INPUT_KEY_BACKPRESSURE_LOGGER_ID_PREFIX = 'backpressure-logger-id-prefix';

    /**
     * Backpressure logger types
     */
    private const VALID_BACKPRESSURE_LOGGER_OPTIONS = [
        RedisRequestLogger::BACKPRESSURE_LOGGER_REDIS,
    ];

    /**
     * Config paths map to input keys
     */
    private const CONFIG_PATH_TO_INPUT_KEY_MAP = [
        RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_SERVER,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PORT,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_TIMEOUT,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PERSISTENT,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_DB,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PASSWORD,
        RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_USER,
        RedisRequestLogger::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX =>
            self::INPUT_KEY_BACKPRESSURE_LOGGER_ID_PREFIX,
    ];

    /**
     * @var RedisConnectionValidator
     */
    private RedisConnectionValidator $redisValidator;

    /**
     * @param RedisConnectionValidator $redisValidator
     */
    public function __construct(RedisConnectionValidator $redisValidator)
    {
        $this->redisValidator = $redisValidator;
    }

    /**
     * @inheritDoc
     */
    public function getOptions()
    {
        return [
            new SelectConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER,
                SelectConfigOption::FRONTEND_WIZARD_SELECT,
                self::VALID_BACKPRESSURE_LOGGER_OPTIONS,
                RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER,
                'Backpressure logger handler'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_SERVER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER,
                'Redis server'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PORT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT,
                'Redis server listen port'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_TIMEOUT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT,
                'Redis server timeout'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PERSISTENT,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT,
                'Redis persistent'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_DB,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB,
                'Redis db number'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_PASSWORD,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD,
                'Redis server password'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_REDIS_USER,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisClient::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER,
                'Redis server user'
            ),
            new TextConfigOption(
                self::INPUT_KEY_BACKPRESSURE_LOGGER_ID_PREFIX,
                TextConfigOption::FRONTEND_WIZARD_TEXT,
                RedisRequestLogger::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX,
                'ID prefix for keys'
            ),
        ];
    }

    /**
     * @inheritDoc
     *
     * @throws FileSystemException|RuntimeException;
     */
    public function createConfig(array $options, DeploymentConfig $deploymentConfig)
    {
        $configData = new ConfigData(ConfigFilePool::APP_ENV);

        foreach (self::CONFIG_PATH_TO_INPUT_KEY_MAP as $configPath => $inputKey) {
            switch ($inputKey) {
                case self::INPUT_KEY_BACKPRESSURE_LOGGER:
                    $this->configureRequestLogger($options, $configData, $deploymentConfig);
                    break;
                case self::INPUT_KEY_BACKPRESSURE_LOGGER_ID_PREFIX:
                    $this->configureIdPrefix($options, $configData, $deploymentConfig);
                    break;
                default:
                    if (isset($options[$inputKey])) {
                        $configData->set($configPath, $options[$inputKey]);
                    } elseif ($deploymentConfig->get($configPath)) {
                        $configData->set($configPath, $deploymentConfig->get($configPath));
                    }
            }
        }

        return $configData;
    }

    /**
     * Configures the request logger
     *
     * @param array $options
     * @param ConfigData $configData
     * @param DeploymentConfig $deploymentConfig
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function configureRequestLogger(array $options, ConfigData $configData, DeploymentConfig $deploymentConfig)
    {
        $requestLoggerType = $options[self::INPUT_KEY_BACKPRESSURE_LOGGER]
            ?? $deploymentConfig->get(RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER);

        if (RedisRequestLogger::BACKPRESSURE_LOGGER_REDIS !== $requestLoggerType) {
            return;
        }

        $configData->set(
            RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER,
            RedisRequestLogger::BACKPRESSURE_LOGGER_REDIS
        );

        foreach (RedisClient::DEFAULT_REDIS_CONFIG_VALUES as $configPath => $value) {
            if (!$deploymentConfig->get($configPath)) {
                $configData->set($configPath, $value);
            }
        }
    }

    /**
     * Configures the id prefix
     *
     * @param array $options
     * @param ConfigData $configData
     * @param DeploymentConfig $deploymentConfig
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function configureIdPrefix(array $options, ConfigData $configData, DeploymentConfig $deploymentConfig)
    {
        if (isset($options[self::INPUT_KEY_BACKPRESSURE_LOGGER_ID_PREFIX])) {
            $configData->set(
                RedisRequestLogger::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX,
                $options[self::INPUT_KEY_BACKPRESSURE_LOGGER_ID_PREFIX]
            );
            return;
        }

        $criteria = !$deploymentConfig->get(RedisRequestLogger::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX)
            && (
                $deploymentConfig->get(RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER)
                || isset($options[self::INPUT_KEY_BACKPRESSURE_LOGGER])
            );

        if ($criteria) {
            $configData->set(RedisRequestLogger::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX, $this->generatePrefix());
        }
    }

    /**
     * @inheritDoc
     *
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function validate(array $options, DeploymentConfig $deploymentConfig)
    {
        $loggerType = $options[self::INPUT_KEY_BACKPRESSURE_LOGGER]
            ?? $deploymentConfig->get(RequestLoggerInterface::CONFIG_PATH_BACKPRESSURE_LOGGER);

        if ($loggerType) {
            if (RedisRequestLogger::BACKPRESSURE_LOGGER_REDIS === $loggerType) {
                return !$this->validateRedisConfig($options, $deploymentConfig)
                    ? ['Invalid Redis configuration. Could not connect to Redis server.']
                    : [];
            }

            return ["Invalid backpressure request logger type: '{$loggerType}'"];
        }

        return [];
    }

    /**
     * Validate that Redis connection succeeds for given configuration
     *
     * @param array $options
     * @param DeploymentConfig $deploymentConfig
     * @return bool
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function validateRedisConfig(array $options, DeploymentConfig $deploymentConfig): bool
    {
        $config = [];
        foreach (RedisClient::KEY_CONFIG_PATH_MAP as $key => $configPath) {
            $config[$key] = $options[self::CONFIG_PATH_TO_INPUT_KEY_MAP[$configPath]]
                ?? $deploymentConfig->get(
                    $configPath,
                    RedisClient::DEFAULT_REDIS_CONFIG_VALUES[$configPath] ?? null
                );
        }

        return $this->redisValidator->isValidConnection($config);
    }

    /**
     * Generate default cache ID prefix based on installation dir
     *
     * @return string
     */
    private function generatePrefix(): string
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return substr(\hash('sha256', dirname(__DIR__, 6)), 0, 3) . '_';
    }
}
