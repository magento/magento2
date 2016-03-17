<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

/**
 * Class CallbackInvoker to invoke callbacks for consumer classes
 */
class CallbackInvoker
{
    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param int $maxNumberOfMessages
     * @param \Closure $callback
     * @return void
     */
    public function invoke(QueueInterface $queue, $maxNumberOfMessages, $callback)
    {
        for ($i = $maxNumberOfMessages; $i > 0; $i--) {
            $message = $queue->dequeue();
            if ($message === null) {
                break;
            }
            $callback($message);
        }
    }
}
