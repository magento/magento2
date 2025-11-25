<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Lock\LockManagerInterface;

/**
 * @inheritDoc
 */
class CartAddressMutex implements CartAddressMutexInterface
{
    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        LockManagerInterface $lockManager,
    ) {
        $this->lockManager = $lockManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $lockName, callable $callable, int $result, array $args = [])
    {
        if (!$this->lockManager->lock($lockName, 0)) {
            return $result;
        }
        try {
            $result = $callable(...$args);
        } finally {
            $this->lockManager->unlock($lockName);
        }

        return $result;
    }
}
