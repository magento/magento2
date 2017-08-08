<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue;

/**
 * Class CallbackInvoker to invoke callbacks for consumer classes
 * @since 2.1.0
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
     * @since 2.1.0
     */
    public function invoke(QueueInterface $queue, $maxNumberOfMessages, $callback)
    {
        for ($i = $maxNumberOfMessages; $i > 0; $i--) {
            do {
                $message = $queue->dequeue();
            } while ($message === null && (sleep(1) === 0));
            $callback($message);
        }
    }
}
