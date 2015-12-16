<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config\Reader;

use Magento\Framework\MessageQueue\ConfigInterface as QueueConfig;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Communication\Config\Reader\RemoteServiceReader as CommunicationRemoteServiceReader;

/**
 * Remote service configuration reader.
 */
class RemoteServiceReader implements \Magento\Framework\Config\ReaderInterface
{
    const DEFAULT_PUBLISHER = 'default';
    const DEFAULT_CONNECTION = 'amqp';
    const DEFAULT_EXCHANGE = 'magento';
    const DEFAULT_HANDLER = 'default';

    /**
     * @var CommunicationRemoteServiceReader
     */
    private $communicationReader;

    /**
     * Initialize dependencies.
     *
     * @param CommunicationRemoteServiceReader $communicationReader
     */
    public function __construct(
        CommunicationRemoteServiceReader $communicationReader
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
        $queueConsumers = [];
        foreach ($remoteServiceTopics as $topicName => $communicationConfig) {
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

            $consumerName = 'consumer' . ucfirst(str_replace('.', '', $queueName));
            $topicHandlers = [
                self::DEFAULT_HANDLER => [
                    QueueConfig::CONSUMER_HANDLER_TYPE => $communicationConfig[CommunicationConfig::HANDLER_TYPE],
                    QueueConfig::CONSUMER_HANDLER_METHOD => $communicationConfig[CommunicationConfig::HANDLER_METHOD]
                ]
            ];
            $queueConsumers = [
                $consumerName => [
                    QueueConfig::CONSUMER_NAME => $consumerName,
                    QueueConfig::CONSUMER_QUEUE => $queueName,
                    QueueConfig::CONSUMER_CONNECTION => self::DEFAULT_CONNECTION,
                    QueueConfig::CONSUMER_TYPE => $communicationConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]
                        ? QueueConfig::CONSUMER_TYPE_SYNC
                        : QueueConfig::CONSUMER_TYPE_ASYNC,
                    QueueConfig::CONSUMER_HANDLERS => [$topicName => $topicHandlers],
                    QueueConfig::CONSUMER_MAX_MESSAGES => null,
                    QueueConfig::CONSUMER_INSTANCE_TYPE => null
                ]
            ];
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
            QueueConfig::CONSUMERS => $queueConsumers,
            QueueConfig::BINDS => $queueBinds,
            QueueConfig::EXCHANGE_TOPIC_TO_QUEUES_MAP => $queueExchangeTopicToQueueMap,
        ];
    }
}
