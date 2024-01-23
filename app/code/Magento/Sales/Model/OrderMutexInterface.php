<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

/**
 * Intended to prevent race conditions during order update by concurrent requests.
 */
interface OrderMutexInterface
{
    /**
     * Acquires a lock for order, executes callable and releases the lock after.
     *
     * @param int $orderId
     * @param callable $callable
     * @param array $args
     * @return mixed
     */
    public function execute(int $orderId, callable $callable, array $args = []);
}
