<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @inheritDoc
 */
class CustomerCartMutex implements CustomerCartMutexInterface
{
    private const LOCK_PREFIX = 'customer_cart_';
    
    private const LOCK_TIMEOUT = 60;

    /**
     * @param LockManagerInterface $lockManager
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param int $lockWaitTimeout
     */
    public function __construct(
        private readonly LockManagerInterface $lockManager,
        private readonly LoggerInterface $logger,
        private readonly StoreManagerInterface $storeManager,
        private readonly int $lockWaitTimeout = self::LOCK_TIMEOUT
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(int $customerId, int $storeId, callable $callable, array $args = []): mixed
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        $lockName = self::LOCK_PREFIX . $customerId . '_' . $websiteId;

        if (!$this->lockManager->lock($lockName, $this->lockWaitTimeout)) {
            $this->logger->critical(
                'The customer cart is locked, the request has been aborted.'
                . ' Customer ID: ' . $customerId . ', Store ID: ' . $storeId
            );
            throw new CustomerCartMutexException(
                __('The customer cart is locked for processing. Please try again later.')
            );
        }

        try {
            return $callable(...$args);
        } finally {
            $this->lockManager->unlock($lockName);
        }
    }
}
