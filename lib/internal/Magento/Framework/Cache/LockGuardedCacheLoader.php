<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache;

use Magento\Framework\App\ObjectManager;
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
     * List of locked names
     *
     * Used to prevent race condition when lock get released during request
     *
     * @var string[]
     */
    private $lockedNames = [];

    /**
     * @var StaleCacheNotifierInterface
     */
    private $notifier;

    /**
     * @param LockManagerInterface $locker
     * @param int $lockTimeout
     * @param int $delayTimeout
     * @param StaleCacheNotifierInterface $notifier
     */
    public function __construct(
        LockManagerInterface $locker,
        int $lockTimeout = 10000,
        int $delayTimeout = 20,
        StaleCacheNotifierInterface $notifier = null
    ) {
        $this->locker = $locker;
        $this->lockTimeout = $lockTimeout;
        $this->delayTimeout = $delayTimeout;
        $this->notifier = $notifier ?? ObjectManager::getInstance()->get(CompositeStaleCacheNotifier::class);
    }

    /**
     * Loads cache data by blocking till lock is released
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
     * @param callable|null $dataFormatter
     * @return mixed
     */
    public function nonBlockingLockedLoadData(
        string $lockName,
        callable $dataLoader,
        callable $dataCollector,
        callable $dataSaver,
        callable $dataFormatter = null
    ) {
        $cachedData = $dataLoader();

        if ($cachedData !== false) {
            return $cachedData;
        }

        $isLocked = $this->isLocked($lockName);

        // Optimistic load before trying to acquire lock for write
        $cachedData = $dataLoader();
        if ($cachedData !== false) {
            return $cachedData;
        }

        $isLockAcquired = false;

        if (!$isLocked) {
            $isLockAcquired = $this->locker->lock($lockName, 0);
        }

        $cachedData = $dataCollector();

        if ($isLockAcquired) {
            try {
                $cachedData = $dataSaver($cachedData);
            } finally {
                $this->locker->unlock($lockName);
            }
        } else {
            $this->notifier->cacheLoaderIsUsingStaleCache();
        }

        return $dataFormatter ? $dataFormatter($cachedData) : $cachedData;
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

    /**
     * Checks if name is locked
     *
     * Preserves value in locked names list
     * if it was locked to prevent race condition
     *
     * @param string $lockName
     * @return bool
     */
    private function isLocked($lockName)
    {
        if (isset($this->lockedNames[$lockName])) {
            return true;
        }

        if ($this->locker->isLocked($lockName)) {
            $this->lockedNames[$lockName] = $lockName;
            return true;
        }

        return false;
    }
}
