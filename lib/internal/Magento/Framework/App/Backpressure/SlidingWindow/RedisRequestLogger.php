<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\ContextInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\App\Backpressure\SlidingWindow\RedisRequestLogger\RedisClient;

/**
 * Logging requests to Redis
 */
class RedisRequestLogger implements RequestLoggerInterface
{
    /**
     * Identifier for Redis Logger type
     */
    public const BACKPRESSURE_LOGGER_REDIS = 'redis';

    /**
     * Default prefix id
     */
    private const DEFAULT_PREFIX_ID = 'reqlog';

    /**
     * Config path for backpressure logger id prefix
     */
    public const CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX = 'backpressure/logger/id-prefix';

    /**
     * @var RedisClient
     */
    private $redisClient;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param RedisClient $redisClient
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        RedisClient $redisClient,
        DeploymentConfig $deploymentConfig
    ) {
        $this->redisClient = $redisClient;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * @inheritDoc
     */
    public function incrAndGetFor(ContextInterface $context, int $timeSlot, int $discardAfter): int
    {
        $id = $this->generateId($context, $timeSlot);
        $this->redisClient->incrBy($id, 1);
        $this->redisClient->expireAt($id, time() + $discardAfter);

        return (int)$this->redisClient->exec()[0];
    }

    /**
     * @inheritDoc
     */
    public function getFor(ContextInterface $context, int $timeSlot): ?int
    {
        $value = $this->redisClient->get($this->generateId($context, $timeSlot));

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
        return $this->getPrefixId()
            . $context->getTypeId()
            . $context->getIdentityType()
            . $context->getIdentity()
            . $timeSlot;
    }

    /**
     * Returns prefix id
     *
     * @return string
     */
    private function getPrefixId(): string
    {
        try {
            return (string)$this->deploymentConfig->get(
                self::CONFIG_PATH_BACKPRESSURE_LOGGER_ID_PREFIX,
                self::DEFAULT_PREFIX_ID
            );
        } catch (RuntimeException | FileSystemException $e) {
            return self::DEFAULT_PREFIX_ID;
        }
    }
}
