<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger;

use Credis_Client;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Redis client for request logger
 */
class RedisClient
{
    /**
     * Keys for Redis settings
     */
    public const KEY_HOST = 'host';
    public const KEY_PORT = 'port';
    public const KEY_TIMEOUT = 'timeout';
    public const KEY_PERSISTENT = 'persistent';
    public const KEY_DB = 'db';
    public const KEY_PASSWORD = 'password';
    public const KEY_USER = 'user';

    /**
     * Configuration paths for Redis settings
     */
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER = 'backpressure/logger/options/server';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT = 'backpressure/logger/options/port';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT = 'backpressure/logger/options/timeout';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT = 'backpressure/logger/options/persistent';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB = 'backpressure/logger/options/db';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD = 'backpressure/logger/options/password';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER = 'backpressure/logger/options/user';

    /**
     * Redis default settings
     */
    public const DEFAULT_REDIS_CONFIG_VALUES = [
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER => '127.0.0.1',
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT => 6379,
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT => null,
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT => '',
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB => 3,
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD => null,
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER => null,
    ];

    /**
     * Config map
     */
    public const KEY_CONFIG_PATH_MAP = [
        self::KEY_HOST => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER,
        self::KEY_PORT => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT,
        self::KEY_TIMEOUT => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT,
        self::KEY_PERSISTENT => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT,
        self::KEY_DB => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB,
        self::KEY_PASSWORD => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD,
        self::KEY_USER => self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER,
    ];

    /**
     * @var Credis_Client
     */
    private $pipeline;

    /**
     * @param DeploymentConfig $config
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __construct(DeploymentConfig $config)
    {
        $credisClient = new Credis_Client(
            $this->getHost($config),
            $this->getPort($config),
            $this->getTimeout($config),
            $this->getPersistent($config),
            $this->getDb($config),
            $this->getPassword($config),
            $this->getUser($config)
        );

        $this->pipeline = $credisClient->pipeline();
    }

    /**
     * Increments given key value
     *
     * @param string $key
     * @param int $decrement
     * @return Credis_Client|int
     */
    public function incrBy(string $key, int $decrement)
    {
        return $this->pipeline->incrBy($key, $decrement);
    }

    /**
     * Sets expiration date of the key
     *
     * @param string $key
     * @param int $timestamp
     * @return Credis_Client|int
     */
    public function expireAt(string $key, int $timestamp)
    {
        return $this->pipeline->expireAt($key, $timestamp);
    }

    /**
     * Returns value by key
     *
     * @param string $key
     * @return bool|Credis_Client|string
     */
    public function get(string $key)
    {
        return $this->pipeline->get($key);
    }

    /**
     * Execute statement
     *
     * @return array
     */
    public function exec(): array
    {
        return $this->pipeline->exec();
    }

    /**
     * Returns Redis host
     *
     * @param DeploymentConfig $config
     * @return string
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getHost(DeploymentConfig $config): string
    {
        return $config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_SERVER]
        );
    }

    /**
     * Returns Redis port
     *
     * @param DeploymentConfig $config
     * @return int
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getPort(DeploymentConfig $config): int
    {
        return (int)$config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PORT]
        );
    }

    /**
     * Returns Redis timeout
     *
     * @param DeploymentConfig $config
     * @return float|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getTimeout(DeploymentConfig $config): ?float
    {
        return (float)$config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_TIMEOUT]
        );
    }

    /**
     * Returns Redis persistent
     *
     * @param DeploymentConfig $config
     * @return string
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getPersistent(DeploymentConfig $config): string
    {
        return $config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PERSISTENT]
        );
    }

    /**
     * Returns Redis db
     *
     * @param DeploymentConfig $config
     * @return int
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getDb(DeploymentConfig $config): int
    {
        return (int)$config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_DB]
        );
    }

    /**
     * Returns Redis password
     *
     * @param DeploymentConfig $config
     * @return string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getPassword(DeploymentConfig $config): ?string
    {
        return $config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_PASSWORD]
        );
    }

    /**
     * Returns Redis user
     *
     * @param DeploymentConfig $config
     * @return string|null
     * @throws FileSystemException
     * @throws RuntimeException
     */
    private function getUser(DeploymentConfig $config): ?string
    {
        return $config->get(
            self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER,
            self::DEFAULT_REDIS_CONFIG_VALUES[self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_USER]
        );
    }
}
