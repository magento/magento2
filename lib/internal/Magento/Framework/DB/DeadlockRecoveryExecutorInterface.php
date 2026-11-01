<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\DB;

use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Executes database operations with automatic retry handling for deadlock failures in concurrent environments.
 */
interface DeadlockRecoveryExecutorInterface
{
    /**
     * Executes a database operation and automatically retries it when a deadlock error occurs.
     *
     * @param AdapterInterface $connection
     * @param callable $callable
     * @param array $args
     * @return mixed
     */
    public function execute(AdapterInterface $connection, callable $callable, array $args);
}
