<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
