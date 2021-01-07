<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
