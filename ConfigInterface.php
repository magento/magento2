<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

use Magento\Framework\Exception\LocalizedException;

interface ConfigInterface
{
    /**
     * Identify configured exchange for the provided topic.
     *
     * @param string $topicName
     * @return string
     * @throws LocalizedException
     */
    public function getExchangeByTopic($topicName);

    /**
     * Identify a list of all queue names corresponding to the specified topic (and implicitly exchange).
     *
     * @param string $topic
     * @return string[]
     * @throws LocalizedException
     */
    public function getQueuesByTopic($topic);

    /**
     * @param string $topic
     * @return string
     * @throws LocalizedException
     */
    public function getConnectionByTopic($topic);

    /**
     * @param string $consumer
     * @return string
     * @throws LocalizedException
     */
    public function getConnectionByConsumer($consumer);

    /**
     * Identify which option is used to define message schema: data interface or service method params
     *
     * @param string $topic
     * @return string
     */
    public function getMessageSchemaType($topic);

    /**
     * Get all consumer names
     *
     * @return string[]
     */
    public function getConsumerNames();

    /**
     * Get consumer configuration
     *
     * @param string $name
     * @return array|null
     */
    public function getConsumer($name);

    /**
     * Get queue binds
     *
     * @return array
     */
    public function getBinds();

    /**
     * Get publishers
     *
     * @return array
     */
    public function getPublishers();

    /**
     * Get consumers
     *
     * @return array
     */
    public function getConsumers();

    /**
     * Get topic config
     * @param string $name
     *
     * @return array
     */
    public function getTopic($name);

    /**
     * Get published config
     * @param string $name
     *
     * @return array
     */
    public function getPublisher($name);

    /**
     * Get queue name for response
     *
     * @param string $topicName
     * @return string
     */
    public function getResponseQueueName($topicName);
}
