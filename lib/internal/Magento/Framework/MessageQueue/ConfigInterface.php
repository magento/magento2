<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

/**
 * @deprecated 102.0.5
 */
interface ConfigInterface
{
    const PUBLISHERS = 'publishers';
    const PUBLISHER_NAME = 'name';
    const PUBLISHER_CONNECTION = 'connection';
    const PUBLISHER_EXCHANGE = 'exchange';

    const TOPICS = 'topics';
    const TOPIC_NAME = 'name';
    const TOPIC_PUBLISHER = 'publisher';
    const TOPIC_SCHEMA = 'schema';
    const TOPIC_RESPONSE_SCHEMA = 'response_schema';
    const TOPIC_SCHEMA_TYPE = 'schema_type';
    const TOPIC_SCHEMA_VALUE = 'schema_value';

    const TOPIC_SCHEMA_TYPE_OBJECT = 'object';
    const TOPIC_SCHEMA_TYPE_METHOD = 'method_arguments';

    const SCHEMA_METHOD_PARAM_NAME = 'param_name';
    const SCHEMA_METHOD_PARAM_POSITION = 'param_position';
    const SCHEMA_METHOD_PARAM_TYPE = 'param_type';
    const SCHEMA_METHOD_PARAM_IS_REQUIRED = 'is_required';

    const CONSUMERS = 'consumers';
    const CONSUMER_NAME = 'name';
    const CONSUMER_QUEUE = 'queue';
    const CONSUMER_CONNECTION = 'connection';
    const CONSUMER_INSTANCE_TYPE = 'instance_type';
    const CONSUMER_CLASS = 'type';
    const CONSUMER_METHOD = 'method';
    const CONSUMER_MAX_MESSAGES = 'max_messages';
    const CONSUMER_HANDLERS = 'handlers';
    const CONSUMER_HANDLER_TYPE = 'type';
    const CONSUMER_HANDLER_METHOD = 'method';
    const CONSUMER_TYPE = 'consumer_type';
    const CONSUMER_TYPE_SYNC = 'sync';
    const CONSUMER_TYPE_ASYNC = 'async';

    const RESPONSE_QUEUE_PREFIX = 'responseQueue.';

    const BINDS = 'binds';
    const BIND_QUEUE = 'queue';
    const BIND_EXCHANGE = 'exchange';
    const BIND_TOPIC = 'topic';

    const BROKER_TOPIC = 'topic';
    const BROKER_TYPE = 'type';
    const BROKER_EXCHANGE = 'exchange';
    const BROKER_CONSUMERS = 'consumers';
    const BROKER_CONSUMER_NAME = 'name';
    const BROKER_CONSUMER_QUEUE = 'queue';
    const BROKER_CONSUMER_INSTANCE_TYPE = 'instance_type';
    const BROKER_CONSUMER_MAX_MESSAGES = 'max_messages';
    const BROKERS = 'brokers';

    /**
     * Map which allows optimized search of queues corresponding to the specified exchange and topic pair.
     */
    const EXCHANGE_TOPIC_TO_QUEUES_MAP = 'exchange_topic_to_queues_map';

    /**
     * Identify configured exchange for the provided topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getExchangeByTopic($topicName);

    /**
     * Identify a list of all queue names corresponding to the specified topic (and implicitly exchange).
     *
     * @param string $topic
     * @return string[]
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Topology\ConfigInterface::getQueues
     */
    public function getQueuesByTopic($topic);

    /**
     * @param string $topic
     * @return string
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getConnectionByTopic($topic);

    /**
     * @param string $consumer
     * @return string
     * @throws LocalizedException
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumer
     */
    public function getConnectionByConsumer($consumer);

    /**
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $topic
     * @return string
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     */
    public function getMessageSchemaType($topic);

    /**
     * Get all consumer names
     *
     * @return string[]
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumers
     */
    public function getConsumerNames();

    /**
     * Get consumer configuration
     *
     * @param string $name
     * @return array|null
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumer
     */
    public function getConsumer($name);

    /**
     * Get queue binds
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Topology\ConfigInterface::getExchanges
     */
    public function getBinds();

    /**
     * Get publishers
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublishers
     */
    public function getPublishers();

    /**
     * Get consumers
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Consumer\ConfigInterface::getConsumers
     */
    public function getConsumers();

    /**
     * Get topic config
     *
     * @param string $name
     * @return array
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getTopic($name);

    /**
     * Get published config
     * @param string $name
     *
     * @return array
     * @see \Magento\Framework\MessageQueue\Publisher\ConfigInterface::getPublisher
     */
    public function getPublisher($name);

    /**
     * Get queue name for response
     *
     * @param string $topicName
     * @return string
     * @see \Magento\Framework\MessageQueue\Rpc\ResponseQueueNameBuilder::getQueueName
     */
    public function getResponseQueueName($topicName);
}
