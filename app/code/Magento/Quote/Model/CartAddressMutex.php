<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
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
        LockManagerInterface $lockManager
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
