<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

/**
 * Callback invoker interface. Invoke callbacks for consumer classes.
 */
interface CallbackInvokerInterface
{
    /**
     * Run short running process
     *
     * @param QueueInterface $queue
     * @param int $maxNumberOfMessages
     * @param \Closure $callback
     * @param int|null $maxIdleTime
     * @param int|null $sleep
     * @return void
     */
    public function invoke(
        QueueInterface $queue,
        $maxNumberOfMessages,
        $callback,
        $maxIdleTime = null,
        $sleep = null
    );
}
