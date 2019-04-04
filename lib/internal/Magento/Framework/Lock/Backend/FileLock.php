<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Phrase;

/**
 * LockManager using the file system for locks
 */
class FileLock implements LockManagerInterface
{
    /**
     * The file driver instance
     *
     * @var FileDriver
     */
    private $fileDriver;

    /**
     * The path to the locks storage folder
     *
     * @var string
     */
    private $path;

    /**
     * How many microseconds to wait before re-try to acquire a lock
     *
     * @var int
     */
    private $sleepCycle = 100000;

    /**
     * The mapping list of the path lock with the file resource
     *
     * @var array
     */
    private $locks = [];

    /**
     * @param FileDriver $fileDriver The file driver
     * @param string $path The path to the locks storage folder
     * @throws RuntimeException Throws RuntimeException if $path is empty
     *         or cannot create the directory for locks
     */
    public function __construct(FileDriver $fileDriver, string $path)
    {
        if (!$path) {
            throw new RuntimeException(new Phrase('The path needs to be a non-empty string.'));
        }

        $this->fileDriver = $fileDriver;
        $this->path = rtrim($path, '/') . '/';

        try {
            if (!$this->fileDriver->isExists($this->path)) {
                $this->fileDriver->createDirectory($this->path);
            }
        } catch (FileSystemException $exception) {
            throw new RuntimeException(
                new Phrase('Cannot create the directory for locks: %1', [$this->path]),
                $exception
            );
        }
    }

    /**
     * Acquires a lock by name
     *
     * @param string $name The lock name
     * @param int $timeout Timeout in seconds. A negative timeout value means infinite timeout
     * @return bool Returns true if the lock is acquired, otherwise returns false
     * @throws RuntimeException Throws RuntimeException if cannot acquires the lock because FS problems
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        try {
            $lockFile = $this->getLockPath($name);
            $fileResource = $this->fileDriver->fileOpen($lockFile, 'w+');
            $skipDeadline = $timeout < 0;
            $deadline = microtime(true) + $timeout;

            while (!$this->tryToLock($fileResource)) {
                if (!$skipDeadline && $deadline <= microtime(true)) {
                    $this->fileDriver->fileClose($fileResource);
                    return false;
                }
                usleep($this->sleepCycle);
            }
        } catch (FileSystemException $exception) {
            throw new RuntimeException(new Phrase('Cannot acquire a lock.'), $exception);
        }

        $this->locks[$lockFile] = $fileResource;
        return true;
    }

    /**
     * Checks if a lock exists by name
     *
     * @param string $name The lock name
     * @return bool Returns true if the lock exists, otherwise returns false
     * @throws RuntimeException Throws RuntimeException if cannot check that the lock exists
     */
    public function isLocked(string $name): bool
    {
        $lockFile = $this->getLockPath($name);
        $result = false;

        try {
            if ($this->fileDriver->isExists($lockFile)) {
                $fileResource = $this->fileDriver->fileOpen($lockFile, 'w+');
                if ($this->tryToLock($fileResource)) {
                    $result = false;
                } else {
                    $result = true;
                }
                $this->fileDriver->fileClose($fileResource);
            }
        } catch (FileSystemException $exception) {
            throw new RuntimeException(new Phrase('Cannot verify that the lock exists.'), $exception);
        }

        return $result;
    }

    /**
     * Remove the lock by name
     *
     * @param string $name The lock name
     * @return bool If the lock is removed returns true, otherwise returns false
     */
    public function unlock(string $name): bool
    {
        $lockFile = $this->getLockPath($name);

        if (isset($this->locks[$lockFile]) && $this->tryToUnlock($this->locks[$lockFile])) {
            unset($this->locks[$lockFile]);
            return true;
        }

        return false;
    }

    /**
     * Returns the full path to the lock file by name
     *
     * @param string $name The lock name
     * @return string The path to the lock file
     */
    private function getLockPath(string $name): string
    {
        return $this->path . $name;
    }

    /**
     * Tries to lock a file resource
     *
     * @param resource $resource The file resource
     * @return bool If the lock is acquired returns true, otherwise returns false
     */
    private function tryToLock($resource): bool
    {
        try {
            return $this->fileDriver->fileLock($resource, LOCK_EX | LOCK_NB);
        } catch (FileSystemException $exception) {
            return false;
        }
    }

    /**
     * Tries to unlock a file resource
     *
     * @param resource $resource The file resource
     * @return bool If the lock is removed returns true, otherwise returns false
     */
    private function tryToUnlock($resource): bool
    {
        try {
            return $this->fileDriver->fileLock($resource, LOCK_UN | LOCK_NB);
        } catch (FileSystemException $exception) {
            return false;
        }
    }
}
