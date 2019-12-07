<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Lock\LockManagerInterface;

/**
 * Clean generated code, DI configuration and cache folders
 */
class GeneratedFiles
{
    /**
     * Regenerate flag file name
     */
    const REGENERATE_FLAG = '/var/.regenerate';

    /**
     * Regenerate lock file name
     */
    const REGENERATE_LOCK = self::REGENERATE_FLAG . '.lock';

    /**
     * Acquire regenerate lock timeout
     */
    const REGENERATE_LOCK_TIMEOUT = 5;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var WriteInterface
     */
    private $write;

    /**
     * @var LockManagerInterface
     */
    private $lockManager;

    /**
     * GeneratedFiles constructor.
     *
     * @param DirectoryList $directoryList
     * @param WriteFactory $writeFactory
     * @param LockManagerInterface $lockManager
     */
    public function __construct(
        DirectoryList $directoryList,
        WriteFactory $writeFactory,
        LockManagerInterface $lockManager
    ) {
        $this->directoryList = $directoryList;
        $this->write = $writeFactory->create(BP);
        $this->lockManager = $lockManager;
    }

    /**
     * Create flag for cleaning up generated content
     *
     * @return void
     */
    public function requestRegeneration()
    {
        $this->write->touch(self::REGENERATE_FLAG);
    }

    /**
     * Clean generated code, generated metadata and cache directories
     *
     * @return void
     *
     * @deprecated 100.1.0
     * @see \Magento\Framework\Code\GeneratedFiles::cleanGeneratedFiles
     */
    public function regenerate()
    {
        $this->cleanGeneratedFiles();
    }

    /**
     * Clean generated code, generated metadata and cache directories
     *
     * @return void
     */
    public function cleanGeneratedFiles()
    {
        if ($this->isCleanGeneratedFilesAllowed() && $this->acquireLock()) {
            try {
                $this->write->delete(self::REGENERATE_FLAG);
                $this->deleteFolder(DirectoryList::GENERATED_CODE);
                $this->deleteFolder(DirectoryList::GENERATED_METADATA);
                $this->deleteFolder(DirectoryList::CACHE);
            } catch (FileSystemException $exception) {
                // A filesystem error occurred, possible concurrency error while trying
                // to delete a generated folder being used by another process.
                // Request regeneration for the next and unlock
                $this->requestRegeneration();
            } finally {
                $this->lockManager->unlock(self::REGENERATE_LOCK);
            }
        }
    }

    /**
     * Clean generated files is allowed if requested and not locked
     *
     * @return bool
     */
    private function isCleanGeneratedFilesAllowed(): bool
    {
        try {
            $isAllowed = $this->write->isExist(self::REGENERATE_FLAG)
                && !$this->lockManager->isLocked(self::REGENERATE_LOCK);
        } catch (FileSystemException | RuntimeException $e) {
            // Possible filesystem problem
            $isAllowed = false;
        }

        return $isAllowed;
    }

    /**
     * Acquire lock for performing operations
     *
     * @return bool
     */
    private function acquireLock(): bool
    {
        try {
            $lockAcquired = $this->lockManager->lock(self::REGENERATE_LOCK, self::REGENERATE_LOCK_TIMEOUT);
        } catch (RuntimeException $exception) {
            // Lock not acquired due to possible filesystem problem
            $lockAcquired = false;
        }

        return $lockAcquired;
    }

    /**
     * Delete folder by path
     *
     * @param string $pathType
     * @return void
     */
    private function deleteFolder(string $pathType): void
    {
        $relativePath = $this->write->getRelativePath($this->directoryList->getPath($pathType));
        if ($this->write->isDirectory($relativePath)) {
            $this->write->delete($relativePath);
        }
    }
}
