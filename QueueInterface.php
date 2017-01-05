<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * Reject message
     *
     * @param EnvelopeInterface $envelope
     * @param bool $requeue
     * @param string $rejectionMessage
     * @return void
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null);

    /**
     * Push message to queue directly, without using exchange
     *
     * @param EnvelopeInterface $envelope
     * @return void
     */
    public function push(EnvelopeInterface $envelope);
}
