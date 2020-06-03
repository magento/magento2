<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\App\DeploymentConfig;

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
     * Timeout for information to be collected and saved.
     * If timeout passed that means that data cannot be saved right now.
     * And we will just return collected data.
     *
     * Value of the variable in milliseconds.
     *
     * @var int
     */
    private $loadTimeout;

    /**
     * Minimal delay timeout in ms.
     *
     * @var int
     */
    private $minimalDelayTimeout;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Option that allows to switch off blocking for parallel generation.
     *
     * @var string
     */
    private const CONFIG_NAME_ALLOW_PARALLEL_CACHE_GENERATION = 'allow_parallel_generation';

    /**
     * Config value of parallel generation.
     *
     * @var bool
     */
    private $allowParallelGenerationConfigValue;

    /**
     * LockGuardedCacheLoader constructor.
     * @param LockManagerInterface $locker
     * @param int $lockTimeout
     * @param int $delayTimeout
     * @param int $loadTimeout
     * @param int $minimalDelayTimeout
     * @param DeploymentConfig|null $deploymentConfig
     */
    public function __construct(
        LockManagerInterface $locker,
        int $lockTimeout = 10000,
        int $delayTimeout = 20,
        int $loadTimeout = 10000,
        int $minimalDelayTimeout = 5,
        DeploymentConfig $deploymentConfig = null
    ) {
        $this->locker = $locker;
        $this->lockTimeout = $lockTimeout;
        $this->delayTimeout = $delayTimeout;
        $this->loadTimeout = $loadTimeout;
        $this->minimalDelayTimeout = $minimalDelayTimeout;
        $this->deploymentConfig = $deploymentConfig ?? ObjectManager::getInstance()->get(DeploymentConfig::class);
    }

    /**
     * Load data.
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
        $deadline = microtime(true) + $this->loadTimeout / 100;

        if (empty($this->allowParallelGenerationConfigValue)) {
            $cacheConfig = $this
                ->deploymentConfig
                ->getConfigData('cache');
            $this->allowParallelGenerationConfigValue = $cacheConfig[self::CONFIG_NAME_ALLOW_PARALLEL_CACHE_GENERATION]
                ?? false;
        }

        while ($cachedData === false) {
            if ($deadline <= microtime(true)) {
                return $dataCollector();
            }

            if ($this->locker->lock($lockName, $this->lockTimeout / 1000)) {
                try {
                    $data = $dataCollector();
                    $dataSaver($data);
                    $cachedData = $data;
                } finally {
                    $this->locker->unlock($lockName);
                }
            } elseif ($this->allowParallelGenerationConfigValue === true) {
                return $dataCollector();
            }

            if ($cachedData === false) {
                usleep($this->getLookupTimeout() * 1000);
                $cachedData = $dataLoader();
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
            usleep($this->getLookupTimeout() * 1000);
        }

        $dataCleaner();
    }

    /**
     * Delay will be applied as rand($minimalDelayTimeout, $delayTimeout).
     * This helps to desynchronize multiple clients trying
     * to acquire the lock for the same resource at the same time
     *
     * @return int
     */
    private function getLookupTimeout()
    {
        return rand($this->minimalDelayTimeout, $this->delayTimeout);
    }
}
