<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Cron;

use Magento\Framework\Lock\Backend\FileLock;
use Magento\Framework\Lock\LockBackendFactory;
use Psr\Log\LoggerInterface;

class CleanLocks
{
    /**
     * @param LockBackendFactory $lockFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly LockBackendFactory $lockFactory,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Cron job to cleanup old locks
     */
    public function execute(): void
    {
        $locker = $this->lockFactory->create();

        if ($locker instanceof FileLock) {
            $numberOfLockFilesDeleted = $locker->cleanupOldLocks();

            $this->logger->info(sprintf('Deleted %d old lock files', $numberOfLockFilesDeleted));
        }
    }
}
