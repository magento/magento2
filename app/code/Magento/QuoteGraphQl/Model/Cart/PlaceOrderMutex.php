<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\QuoteGraphQl\Model\Cart;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * @inheritdoc
 */
class PlaceOrderMutex implements PlaceOrderMutexInterface
{
    private const LOCK_PREFIX = 'quote_lock_';

    private const LOCK_TIMEOUT = 10;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var int
     */
    private $lockWaitTimeout;

    /**
     * @param LockManagerInterface $lockManager
     * @param int $lockWaitTimeout
     */
    public function __construct(
        LockManagerInterface $lockManager,
        int $lockWaitTimeout = self::LOCK_TIMEOUT
    ) {
        $this->lockManager = $lockManager;
        $this->lockWaitTimeout = $lockWaitTimeout;
    }

    /**
     * @inheritDoc
     */
    public function execute(string $maskedId, callable $callable, array $args = [])
    {
        if (empty($maskedId)) {
            throw new \InvalidArgumentException('Quote masked id must be provided');
        }

        if ($this->lockManager->isLocked(self::LOCK_PREFIX . $maskedId)) {
            throw new GraphQlAlreadyExistsException(
                __('The order has already been placed and is currently processing.')
            );
        }

        if ($this->lockManager->lock(self::LOCK_PREFIX . $maskedId, $this->lockWaitTimeout)) {
            try {
                return $callable(...$args);
            } finally {
                $this->lockManager->unlock(self::LOCK_PREFIX . $maskedId);
            }
        } else {
            throw new LocalizedException(
                __('Could not acquire lock for the quote id: %1', $maskedId)
            );
        }
    }
}
