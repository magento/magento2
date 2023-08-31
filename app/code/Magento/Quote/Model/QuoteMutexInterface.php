<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

/**
 * Intended to prevent race conditions during quote update by concurrent requests.
 */
interface QuoteMutexInterface
{
    /**
     * Acquires a lock for quote, executes callable and releases the lock after.
     *
     * @param string[] $maskedIds
     * @param callable $callable
     * @param array $args
     * @return mixed
     */
    public function execute(array $maskedIds, callable $callable, array $args = []);
}
