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
     * @return string
     */
    public function getConsumerName();

    /**
     * @return string
     */
    public function getQueueName();

    /**
     * @return string
     */
    public function getExchangeName();

    /**
     * @return string
     */
    public function getTopicName();

    /**
     * @return callback
     */
    public function getCallback();
}
