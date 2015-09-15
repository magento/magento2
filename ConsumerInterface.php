<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Consumers will connect to a queue, read messages, and invoke a method to process the message contents.
 */
interface ConsumerInterface
{
    /**
     * Configure will be called before process to allow the consumer to setup itself.
     *
     * @param ConsumerConfigurationInterface $configuration
     * @return void
     */
    public function configure(ConsumerConfigurationInterface $configuration);

    /**
     * Connects to a queue, consumes a message on the queue, and invoke a method to process the message contents.
     *
     * @param int|null $maxNumberOfMessages if not specified - process all queued incoming messages and terminate,
     *      otherwise terminate execution after processing the specified number of messages
     * @param bool|null $daemonMode
     * @return void
     */
    public function process($maxNumberOfMessages = null, $daemonMode = false);
}
