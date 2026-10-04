<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue;

use Countable;

/**
 * {@inheritdoc}
 *
 * Queue driver that implements this interface must implement count() method
 * that returns the number of pending messages in the queue
 */
interface CountableQueueInterface extends QueueInterface, Countable
{
    /**
     * Get number of pending messages in the queue
     *
     * @return int
     */
    public function count(): int;
}
