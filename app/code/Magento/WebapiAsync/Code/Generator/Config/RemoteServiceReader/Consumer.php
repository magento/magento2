<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;
use Magento\Framework\MessageQueue\DefaultValueProvider;

/**
 * Remote service reader with auto generated configuration for queue_consumer.xml
 */
class Consumer implements \Magento\Framework\Config\ReaderInterface
{
    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * @param DefaultValueProvider $defaultValueProvider
     */
    public function __construct(
        DefaultValueProvider $defaultValueProvider
    ) {
        $this->defaultValueProvider = $defaultValueProvider;
    }

    /**
     * Generate consumer configuration based on remote services declarations
     *
     * @param string|null $scope
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function read($scope = null)
    {
        $result = [];
        $topicName = 'async.operations.all';
        $result[$topicName] =
            [
                'name'             => $topicName,
                'queue'            => $topicName,
                'consumerInstance' => WebApiAsyncConfig::DEFAULT_CONSUMER_INSTANCE,
                'connection'       => $this->getConnection(),
                'maxMessages'      => WebApiAsyncConfig::DEFAULT_CONSUMER_MAX_MESSAGE,
                'handlers'         => [],
                'maxIdleTime'      => null,
                'sleep'            => null,
                'onlySpawnWhenMessageAvailable' => null
            ];

        return $result;
    }

    /**
     * Get connection
     *
     * @return string
     */
    private function getConnection()
    {
        $connection = $this->defaultValueProvider->getConnection();
        // if db connection, return amqp instead.
        return $connection === 'db' ? WebApiAsyncConfig::DEFAULT_CONSUMER_CONNECTION : $connection;
    }
}
