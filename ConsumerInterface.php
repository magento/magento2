<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Consumers will process messages by connecting to a queue, read messages, and invoke a method to
 * process the message contents.
 */
interface ConsumerInterface
{
    /**
     * @param array $configuration
     * @return void
     */
    public function configure($configuration);

    /**
     * Connects to a queue, consumes a message on the queue, and invoke a method to process the message contents.
     * @return void
     */
    public function process();
}