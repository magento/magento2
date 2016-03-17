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
}
