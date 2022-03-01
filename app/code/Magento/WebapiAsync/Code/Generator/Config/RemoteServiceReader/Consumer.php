<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Code\Generator\Config\RemoteServiceReader;

use Magento\AsynchronousOperations\Model\ConfigInterface as WebApiAsyncConfig;

/**
 * Remote service reader with auto generated configuration for queue_consumer.xml
 */
class Consumer implements \Magento\Framework\Config\ReaderInterface
{
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
                'connection'       => WebApiAsyncConfig::DEFAULT_CONSUMER_CONNECTION,
                'maxMessages'      => WebApiAsyncConfig::DEFAULT_CONSUMER_MAX_MESSAGE,
                'handlers'         => [],
                'maxIdleTime'      => null,
                'sleep'            => null,
                'onlySpawnWhenMessageAvailable' => null
            ];

        return $result;
    }
}
