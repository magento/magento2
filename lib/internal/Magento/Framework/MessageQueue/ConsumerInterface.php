<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Consumers will connect to a queue, read messages, and invoke a method to process the message contents.
 *
 * @api
 * @since 102.0.3
 * @since 100.0.2
 */
interface ConsumerInterface
{
    /**
     * Connects to a queue, consumes a message on the queue, and invoke a method to process the message contents.
     *
     * @param int|null $maxNumberOfMessages if not specified - process all queued incoming messages and terminate,
     *      otherwise terminate execution after processing the specified number of messages
     * @return void
     * @since 102.0.3
     */
    public function process($maxNumberOfMessages = null);
}
