<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache;

use Magento\Framework\Lock\LockManagerInterface;

/**
 * Default mutex that provide concurrent access to cache storage.
 */
class LockGuardedCacheLoader
{
    /**
     * @var LockManagerInterface
     */
    private $locker;

    /**
     * Lifetime of the lock for write in cache.
     *
     * Value of the variable in milliseconds.
     *
     * @var int
     */
    private $lockTimeout;

    /**
     * Timeout between retrieves to load the configuration from the cache.
     *
     * Value of the variable in milliseconds.
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
        int $lockTimeout = 10000,
        int $delayTimeout = 20
    ) {
        $this->locker = $locker;
        $this->lockTimeout = $lockTimeout;
        $this->delayTimeout = $delayTimeout;
    }

    /**
     * Loads cache data by blocking till lock is released
     *
     *
     * @param string $lockName
     * @param callable $dataLoader
     * @param callable $dataCollector
     * @param callable $dataSaver
     * @return mixed
     */
    public function lockedLoadData(
        string $lockName,
        callable $dataLoader,
        callable $dataCollector,
        callable $dataSaver
    ) {
        $cachedData = $dataLoader(); //optimistic read

        while ($cachedData === false && $this->locker->isLocked($lockName)) {
            usleep($this->delayTimeout * 1000);
            $cachedData = $dataLoader();
        }

        while ($cachedData === false) {
            try {
                if ($this->locker->lock($lockName, $this->lockTimeout / 1000)) {
                    $data = $dataCollector();
                    $dataSaver($data);
                    $cachedData = $data;
                }
            } finally {
                $this->locker->unlock($lockName);
            }

            if ($cachedData === false) {
                usleep($this->delayTimeout * 1000);
                $cachedData = $dataLoader();
            }
        }

        return $cachedData;
    }


    /**
     * Loads cached data in non blocking way
     *
     * When data is not cached it will try to grab a lock.
     * If lock is not obtainable it just returns back uncached data,
     * so connection is not going into cyclic deadlock
     *
     * @param string $lockName
     * @param callable $dataLoader
     * @param callable $dataCollector
     * @param callable $dataSaver
     * @return mixed
     */
    public function nonBlockingLockedLoadData(
        string $lockName,
        callable $dataLoader,
        callable $dataCollector,
        callable $dataSaver
    ) {
        $cachedData = $dataLoader();

        if ($cachedData !== false) {
            return $cachedData;
        }

        $cachedData = $dataCollector();

        if ($this->locker->lock($lockName, $this->lockTimeout / 1000)) {
            try {
                $cachedData = $dataSaver($cachedData);
            } finally {
                $this->locker->unlock($lockName);
            }
        }

        return $cachedData;
    }

    /**
     * Clean data.
     *
     * @param string $lockName
     * @param callable $dataCleaner
     * @return void
     */
    public function lockedCleanData(string $lockName, callable $dataCleaner)
    {
        while ($this->locker->isLocked($lockName)) {
            usleep($this->delayTimeout * 1000);
        }
        try {
            if ($this->locker->lock($lockName, $this->lockTimeout / 1000)) {
                $dataCleaner();
            }
        } finally {
            $this->locker->unlock($lockName);
        }
    }
}
