<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Model;

use Magento\Framework\Indexer\ConfigInterface;
use Magento\Framework\Indexer\IndexMutexException;
use Magento\Framework\Indexer\IndexMutexInterface;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Intended to prevent race conditions between indexers using the same index table.
 */
class IndexMutex implements IndexMutexInterface
{
    private const LOCK_PREFIX = 'indexer_lock_';

    private const LOCK_TIMEOUT = 60;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var int
     */
    private $lockWaitTimeout;

    /**
     * @param LockManagerInterface $lockManager
     * @param ConfigInterface $config
     * @param int $lockWaitTimeout
     */
    public function __construct(
        LockManagerInterface $lockManager,
        ConfigInterface $config,
        int $lockWaitTimeout = self::LOCK_TIMEOUT
    ) {
        $this->lockManager = $lockManager;
        $this->lockWaitTimeout = $lockWaitTimeout;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $indexerName, callable $callback): void
    {
        $lockName = $indexerName;
        $indexerConfig = $this->config->getIndexer($indexerName);
        if (isset($indexerConfig['shared_index'])) {
            $lockName = $indexerConfig['shared_index'];
        }

        if ($this->lockManager->lock(self::LOCK_PREFIX . $lockName, $this->lockWaitTimeout)) {
            try {
                $callback();
            } finally {
                $this->lockManager->unlock(self::LOCK_PREFIX . $lockName);
            }
        } else {
            throw new IndexMutexException($indexerName);
        }
    }
}
