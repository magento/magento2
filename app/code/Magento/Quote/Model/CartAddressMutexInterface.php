<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

/**
 * Intended to prevent race conditions during quote address processing by concurrent requests.
 */
interface CartAddressMutexInterface
{
    /**
     * Acquires a lock for quote address, executes callable and releases the lock after.
     *
     * @param string $lockName
     * @param callable $callable
     * @param int $result
     * @param array $args
     * @return mixed
     */
    public function execute(string $lockName, callable $callable, int $result, array $args = []);
}
