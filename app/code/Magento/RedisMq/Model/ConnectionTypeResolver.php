<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RedisMq\Model;

use Magento\Framework\MessageQueue\ConnectionTypeResolverInterface;

/**
 * Redis connection type resolver.
 */
class ConnectionTypeResolver implements ConnectionTypeResolverInterface
{
    /**
     *
     */
    const REDIS = 'redis';

    /**
     * Redis connection names.
     *
     * @var string[]
     */
    private $redisConnectionNames;

    /**
     * Initialize dependencies.
     *
     * @param string[] $redisConnectionNames
     */
    public function __construct(array $redisConnectionNames = [])
    {
        $this->redisConnectionNames = $redisConnectionNames;
        $this->redisConnectionNames[] = self::REDIS;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionType($connectionName)
    {
        return in_array($connectionName, $this->redisConnectionNames) ? 'redis' : null;
    }
}
