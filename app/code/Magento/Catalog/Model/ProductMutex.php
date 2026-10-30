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
        $lockName = $this->getLockName($sku);
        if ($this->lockManager->lock($lockName, $this->lockWaitTimeout)) {
            try {
                $result = $callable(...$args);
            } finally {
                $this->lockManager->unlock($lockName);
            }
        } else {
            throw new ProductMutexException(
                __('Could not acquire lock for SKU %1', $sku)
            );
        }
        return $result;
    }

    /**
     * Get one lock name for every spelling of a SKU that resolves to the same product
     *
     * The SKU is normalised the way ProductRepository looks it up, since the sku column is case-insensitive
     * while lock names are not. Hashing keeps the name within the 64 characters MySQL allows for a lock.
     *
     * @param string $sku
     * @return string
     */
    private function getLockName(string $sku): string
    {
        return self::LOCK_PREFIX . hash('xxh128', mb_strtolower(trim($sku)));
    }
}
