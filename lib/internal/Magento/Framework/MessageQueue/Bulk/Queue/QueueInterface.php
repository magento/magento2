<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Bulk\Queue;

use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\QueueInterface as BaseQueueInterface;

/**
 * Interface for interaction with bulk message queue.
 *
 * @api
 */
interface QueueInterface
{
    /**
     * Push message to queue directly, without using exchange
     *
     * @param BaseQueueInterface $queue
     * @param string $topic
     * @param EnvelopeInterface[] $envelopes
     * @return array|null
     */
    public function push(BaseQueueInterface $queue, string $topic, array $envelopes): array|null;
}
