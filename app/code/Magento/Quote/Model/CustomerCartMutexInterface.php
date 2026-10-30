<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\StateException;

/**
 * Intended to prevent race conditions during customer cart creation by concurrent requests.
 */
interface CustomerCartMutexInterface
{
    /**
     * Acquires a lock for customer cart creation, executes callable and releases the lock after.
     *
     * @param int $customerId
     * @param int $storeId
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws StateException
     */
    public function execute(int $customerId, int $storeId, callable $callable, array $args = []): mixed;
}
