<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Consumer\Config;

use Magento\Framework\MessageQueue\Consumer\Config\ConsumerConfigItem\HandlerInterface;

/**
 * Items of this class represent config items declared in etc/queue_consumer.xsd
 */
interface ConsumerConfigItemInterface
{
    /**
     * Get consumer name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get connection name.
     *
     * @return string
     */
    public function getConnection();

    /**
     * Get name of the queue current consumer is listening to.
     *
     * @return string
     */
    public function getQueue();

    /**
     * Get consumer class name.
     *
     * @return string
     */
    public function getConsumerInstance();

    /**
     * Get information about custom handlers to be used by current consumer.
     *
     * @return HandlerInterface[]
     */
    public function getHandlers();

    /**
     * Get maximum number of messages to be consumed from queue before terminating consumer.
     * 
     * @return int
     */
    public function getMaxMessages();
}
