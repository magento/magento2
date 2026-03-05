<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Bulk\Queue;

/**
 * Factory class for @see QueueInterface
 *
 * @api
 */
interface QueueFactoryInterface
{
    /**
     * Create queue instance.
     *
     * @param string $queueName
     * @param string $connectionName
     * @return QueueInterface
     */
    public function create(string $queueName, string $connectionName): QueueInterface;
}
