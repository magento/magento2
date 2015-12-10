<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

interface QueueInterface
{
    /**
     * Get message from queue
     *
     * @return EnvelopeInterface
     */
    public function dequeue();

    /**
     * Acknowledge message delivery
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function acknowledge(EnvelopeInterface $envelope);

    /**
     * Wait for messages and dispatch them
     *
     * @param callable|array $callback
     * @return void
     */
    public function subscribe($callback);

    /**
     * Reject message and return it to the original queue
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function reject(EnvelopeInterface $envelope);

    /**
     * Push message to queue directly, without using exchange
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function push(EnvelopeInterface $envelope);
}
