<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\Lock\LockManagerInterface;

class ProductMutex implements ProductMutexInterface
{
    private const LOCK_PREFIX = 'product_mutex_';

    private const LOCK_TIMEOUT = 60;

    /**
     * @param LockManagerInterface $lockManager
     * @param int $lockWaitTimeout
     */
    public function __construct(
        private readonly LockManagerInterface $lockManager,
        private readonly int $lockWaitTimeout = self::LOCK_TIMEOUT
    ) {
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, callable $callable, ...$args): mixed
    {
        if ($this->lockManager->lock(self::LOCK_PREFIX . $sku, $this->lockWaitTimeout)) {
            try {
                $result = $callable(...$args);
            } finally {
                $this->lockManager->unlock(self::LOCK_PREFIX . $sku);
            }
        } else {
            throw new ProductMutexException(
                __('Could not acquire lock for SKU %1', $sku)
            );
        }
        return $result;
    }
}
