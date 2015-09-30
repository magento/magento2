<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

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
     * Get maximum number of message, which will be read by consumer before termination of the process.
     *
     * @return int|null
     */
    public function getMaxMessages();

    /**
     * Get callback method to process message from the queue.
     *
     * @return callback
     */
    public function getCallback();
}
