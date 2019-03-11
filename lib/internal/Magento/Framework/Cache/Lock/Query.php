<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Lock;

use Magento\Framework\Cache\LockQueryInterface;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Default mutex for cache concurrent access.
 */
class Query implements LockQueryInterface
{
    /**
     * @var LockManagerInterface
     */
    private $locker;

    /**
     * Lifetime of the lock for write in cache.
     *
     * Value of the variable in seconds.
     *
     * @var int
     */
    private $lockTimeout;

    /**
     * Timeout between retrieves to load the configuration from the cache.
     *
     * Value of the variable in microseconds.
     *
     * @var int
     */
    private $delayTimeout;

    /**
     * @param LockManagerInterface $locker
     * @param int $lockTimeout
     * @param int $delayTimeout
     */
    public function __construct(
        LockManagerInterface $locker,
        int $lockTimeout = 10,
        int $delayTimeout = 20000
    ) {
        $this->locker = $locker;
        $this->lockTimeout = $lockTimeout;
        $this->delayTimeout = $delayTimeout;
    }

    /**
     * @inheritdoc
     */
    public function lockedLoadData(
        string $lockName,
        callable $dataLoader,
        callable $dataCollector,
        callable $dataSaver,
        callable $dataCleaner,
        bool $flush = false
    ) {
        $cachedData = $dataLoader(); //optimistic read

        while ($cachedData === false && $this->locker->isLocked($lockName)) {
            usleep($this->delayTimeout);
            $cachedData = $dataLoader();
        }

        while ($cachedData === false) {
            try {
                if ($this->locker->lock($lockName, $this->lockTimeout)) {
                    if (!$flush) {
                        $data = $dataCollector();
                        $dataSaver($data);
                        $cachedData = $data;
                    } else {
                        $dataCleaner();
                        $cachedData = [];
                    }
                }
            } finally {
                $this->locker->unlock($lockName);
            }

            if ($cachedData === false) {
                usleep($this->delayTimeout);
                $cachedData = $dataLoader();
            }
        }

        return $cachedData;
    }
}
