<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Indexer;

/**
 * Intended to prevent race conditions between indexers using the same index table.
 */
interface IndexMutexInterface
{
    /**
     * Acquires a lock for an indexer, executes callable and releases the lock after.
     *
     * @param string $indexerName
     * @param callable $callback
     * @throws IndexMutexException
     */
    public function execute(string $indexerName, callable $callback): void;
}
