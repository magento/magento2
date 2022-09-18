<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;
use Credis_Client;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Logging requests to Redis
 */
class RedisRequestLogger implements RequestLoggerInterface
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
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_COMPRESS_DATA = 'backpressure/logger/options/compress-data';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_COMPRESSION_LIB = 'backpressure/logger/options/compression-lib';
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX = 'backpressure/logger/id-prefix';

    /**
     * Identifier for Redis Logger type
     */
    public const VALUE_BACKPRESSURE_LOGGER_REDIS = 'redis';

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
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_COMPRESS_DATA => 0,
        self::CONFIG_PATH_BACKPRESSURE_LOGGER_REDIS_COMPRESSION_LIB => null,
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
     * Valid compression libs
     */
    public const VALID_COMPRESSION_LIBS = ['gzip', 'lzf', 'lz4', 'snappy'];

    /**
     * @var Credis_Client
     */
    private Credis_Client $credisClient;

    /**
     * @var string
     */
    private string $prefixId;

    /**
     * @param DeploymentConfig $config
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __construct(DeploymentConfig $config)
    {
        $this->credisClient = new Credis_Client(
            $this->getHost($config),
            $this->getPort($config),
            $this->getTimeout($config),
            $this->getPersistent($config),
            $this->getDb($config),
            $this->getPassword($config),
            $this->getUser($config)
        );

        $this->prefixId = $config->get(self::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX, '');
    }

    /**
     * @inheritDoc
     */
    public function incrAndGetFor(ContextInterface $context, int $timeSlot, int $discardAfter): int
    {
        $id = $this->generateId($context, $timeSlot);
        $redis = $this->credisClient->pipeline();

        $redis->incrBy($id, 1);
        $redis->expireAt($id, time() + $discardAfter);

        return (int)$redis->exec()[0];
    }

    /**
     * @inheritDoc
     */
    public function getFor(ContextInterface $context, int $timeSlot): ?int
    {
        $value = $this->credisClient->get($this->generateId($context, $timeSlot));

        return $value ? (int)$value : null;
    }

    /**
     * Generate cache ID based on context
     *
     * @param ContextInterface $context
     * @param int $timeSlot
     * @return string
     */
    private function generateId(ContextInterface $context, int $timeSlot): string
    {
        return $this->prefixId
            . $context->getTypeId()
            . $context->getIdentityType()
            . $context->getIdentity()
            . $timeSlot;
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
