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
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 */
class CartMutex implements CartMutexInterface
{
    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LockManagerInterface $lockManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        LockManagerInterface $lockManager,
        LoggerInterface $logger
    ) {
        $this->lockManager = $lockManager;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(int $id, callable $callable, array $args = [])
    {
        $lockName = 'cart_lock_' . $id;

        if (!$this->lockManager->lock($lockName, 0)) {
            $this->logger->critical(
                'The cart is locked for processing, the request has been aborted. Quote ID: ' . $id
            );
            throw new CartLockedException(
                __('The cart is locked for processing. Please try again later.')
            );
        }

        try {
            $result = $callable(...$args);
        } finally {
            $this->lockManager->unlock($lockName);
        }

        return $result;
    }
}
