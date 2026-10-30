<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

/**
 * Prevents race conditions during concurrent product save operations.
 */
interface ProductMutexInterface
{
    /**
     * Acquires a lock for SKU, executes callable and releases the lock after.
     *
     * @param string $sku
     * @param callable $callable
     * @param array $args
     * @return mixed
     * @throws ProductMutexException
     */
    public function execute(string $sku, callable $callable, ...$args): mixed;
}
