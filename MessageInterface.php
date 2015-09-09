<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

/**
 * Messages will store a topic and body, and link to queues.
 */
interface MessageInterface
{
    /**
     * Links the message to the specified queue(s).
     *
     * @param array $queueNames
     * @return void
     */
    public function linkQueues($queueNames);

    /**
     * Finds messages that belong to the specified queue.
     *
     * @param string queueName
     * @return array
     */
    public function getMessages($queueName);
}
