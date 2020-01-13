<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;

/**
 * Remote service configuration reader.
 *
 * @deprecated 102.0.4
 */
class MessageQueue implements \Magento\Framework\Config\ReaderInterface
{
    const DEFAULT_PUBLISHER = 'default';
    const DEFAULT_CONNECTION = 'amqp';
    const DEFAULT_EXCHANGE = 'magento';

    /**
     * @var Communication
     */
    private $communicationReader;

    /**
     * Initialize dependencies.
     *
     * @param Communication $communicationReader
     */
    public function __construct(
        Communication $communicationReader
    ) {
        $this->communicationReader = $communicationReader;
    }

    /**
     * Generate communication configuration based on remote services declarations in di.xml
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        $remoteServiceTopics = $this->communicationReader->read($scope);
        $queueTopics = [];
        $queueBinds = [];
        $queueExchangeTopicToQueueMap = [];
        foreach ($remoteServiceTopics[CommunicationConfig::TOPICS] as $topicName => $communicationConfig) {
            $queueTopics[$topicName] = [
                QueueConfig::TOPIC_NAME => $topicName,
                QueueConfig::TOPIC_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => QueueConfig::TOPIC_SCHEMA_TYPE_METHOD,
                    QueueConfig::TOPIC_SCHEMA_VALUE => $communicationConfig[CommunicationConfig::TOPIC_REQUEST]
                ],
                QueueConfig::TOPIC_RESPONSE_SCHEMA => [
                    QueueConfig::TOPIC_SCHEMA_TYPE => isset($communicationConfig[CommunicationConfig::TOPIC_RESPONSE])
                        ? QueueConfig::TOPIC_SCHEMA_TYPE_OBJECT
                        : null,
                    QueueConfig::TOPIC_SCHEMA_VALUE => $communicationConfig[CommunicationConfig::TOPIC_RESPONSE]
                ],
                QueueConfig::TOPIC_PUBLISHER => self::DEFAULT_PUBLISHER
            ];

            $queueName = 'queue.' . $topicName;
            $queueBinds[$topicName . '--' . self::DEFAULT_EXCHANGE . '--' . $queueName] = [
                QueueConfig::BIND_TOPIC => $topicName,
                QueueConfig::BIND_EXCHANGE => self::DEFAULT_EXCHANGE,
                QueueConfig::BIND_QUEUE => $queueName,
            ];

            $queueExchangeTopicToQueueMap[self::DEFAULT_EXCHANGE . '--' . $topicName] = [$queueName];
        }
        $queuePublishers = [
            self::DEFAULT_PUBLISHER => [
                QueueConfig::PUBLISHER_NAME => self::DEFAULT_PUBLISHER,
                QueueConfig::PUBLISHER_CONNECTION => self::DEFAULT_CONNECTION,
                QueueConfig::PUBLISHER_EXCHANGE => self::DEFAULT_EXCHANGE
            ]
        ];
        return [
            QueueConfig::PUBLISHERS => $queuePublishers,
            QueueConfig::TOPICS => $queueTopics,
            QueueConfig::CONSUMERS => [],
            QueueConfig::BINDS => $queueBinds,
            QueueConfig::EXCHANGE_TOPIC_TO_QUEUES_MAP => $queueExchangeTopicToQueueMap,
        ];
    }
}
