<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
    const MAX_IDLE_TIME = 'max_idle_time';
    const SLEEP = 'sleep';
    const ONLY_SPAWN_WHEN_MESSAGE_AVAILABLE = 'only_spawn_when_message_available';

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
     * @deprecated 103.0.0
     * @see \Magento\Framework\Communication\ConfigInterface::getTopic
     * @throws \LogicException
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
     * @throws \LogicException
     */
    public function getHandlers($topicName);

    /**
     * Get topics.
     *
     * @return string[]
     */
    public function getTopicNames();

    /**
     * Get message schema type.
     *
     * @param string $topicName
     * @return string
     */
    public function getMessageSchemaType($topicName);

    /**
     * Get message queue instance.
     *
     * @return QueueInterface
     */
    public function getQueue();

    /**
     * Get maximal time (in seconds) for waiting new messages from queue before terminating consumer.
     *
     * @return int|null
     */
    public function getMaxIdleTime();

    /**
     * Get time to sleep (in seconds) before checking if a new message is available in the queue.
     *
     * @return int|null
     */
    public function getSleep();

    /**
     * Get is consumer have to be spawned only if there are messages in the queue.
     *
     * @return boolean|null
     */
    public function getOnlySpawnWhenMessageAvailable();
}
