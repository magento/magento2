<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

/**
 * Intended to prevent race conditions during quote processing by concurrent requests.
 */
interface CartMutexInterface
{
    /**
     * Acquires a lock for quote, executes callable and releases the lock after.
     *
     * @param int $id
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws CartLockedException
     */
    public function execute(int $id, callable $callable, array $args = []);
}
