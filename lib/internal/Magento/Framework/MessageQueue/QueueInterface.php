<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Interface for interaction with message queue.
 *
 * @api
 * @since 102.0.1
 * @since 100.0.2
 */
interface QueueInterface
{
    /**
     * Get message from queue
     *
     * @return EnvelopeInterface
     * @since 102.0.1
     */
    public function dequeue();

    /**
     * Acknowledge message delivery
     *
     * @param EnvelopeInterface $envelope
     * @return void
     * @since 102.0.1
     */
    public function acknowledge(EnvelopeInterface $envelope);

    /**
     * Wait for messages and dispatch them
     *
     * @param callable|array $callback
     * @return void
     * @since 102.0.1
     */
    public function subscribe($callback);

    /**
     * Reject message
     *
     * @param EnvelopeInterface $envelope
     * @param bool $requeue
     * @param string $rejectionMessage
     * @return void
     * @since 102.0.1
     */
    public function reject(EnvelopeInterface $envelope, $requeue = true, $rejectionMessage = null);

    /**
     * Push message to queue directly, without using exchange
     *
     * @param EnvelopeInterface $envelope
     * @return void
     * @since 102.0.1
     */
    public function push(EnvelopeInterface $envelope);
}
