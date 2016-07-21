<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Configuration for the consumer.
 */
interface ConsumerConfigurationInterface
{
    const CONSUMER_NAME = "consumer_name";

    const QUEUE_NAME = "queue_name";
    const MAX_MESSAGES = "max_messages";
    const SCHEMA_TYPE = "schema_type";
    const TOPICS = 'topics';
    const TOPIC_TYPE = 'consumer_type';
    const TOPIC_HANDLERS = 'handlers';

    const TYPE_SYNC = 'sync';
    const TYPE_ASYNC = 'async';
    const INSTANCE_TYPE_BATCH = 'batch';
    const INSTANCE_TYPE_SINGULAR = 'singular';

    /**
     * Get consumer name.
     *
     * @return string
     */
    public function getConsumerName();

    /**
     * Get the name of queue which consumer will read from.
     *
     * @return string
     */
    public function getQueueName();

    /**
     * Get consumer type sync|async.
     *
     * @return string
     * @deprecated
     * @see ConsumerConfigurationInterface::getConsumerType
     */
    public function getType();

    /**
     * Get maximum number of message, which will be read by consumer before termination of the process.
     *
     * @return int|null
     */
    public function getMaxMessages();

    /**
     * Get handlers by topic type.
     *
     * @param string $topicName
     * @return callback[]
     */
    public function getHandlers($topicName);

    /**
     * Get topics.
     *
     * @return string[]
     */
    public function getTopicNames();

    /**
     * @param string $topicName
     * @return string
     */
    public function getMessageSchemaType($topicName);

    /**
     * @return QueueInterface
     */
    public function getQueue();

    /**
     * Get handler type (sync/async) based on topic.
     *
     * TODO: Move out from current interface
     *
     * @param string $topicName
     * @return string
     */
    public function getConsumerType($topicName);
}
