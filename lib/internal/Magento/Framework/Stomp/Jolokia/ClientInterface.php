<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Stomp\Jolokia;

interface ClientInterface
{
    /**
     * Check that the broker instance has the Jolokia API available.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Clear queue using Jolokia API. It's much faster than STOMP protocol.
     *
     * @param string $queueName
     * @return int Number of messages cleared
     * @throws RequestFailedException
     */
    public function clearQueue(string $queueName): int;

    /**
     * Get message count for a queue using Jolokia API.
     *
     * @param string $queueName
     * @return int
     * @throws RequestFailedException
     */
    public function getQueueMessageCount(string $queueName): int;
}
