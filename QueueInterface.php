<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Amqp;

interface QueueInterface
{
    /**
     * Get message
     *
     * @return EnvelopeInterface
     */
    public function dequeue();

    /**
     * Queue requires that a message will be acknowledged or it will be moved back
     * into the queue.
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function acknowledge(EnvelopeInterface $envelope);

    /**
     * /**
     * Wait for some expected messages and dispatch to them
     *
     * @param callable|array $callback
     * @return void
     */
    public function subscribe($callback);

    /**
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function reject(EnvelopeInterface $envelope);
}
