<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * Class LockerProcess
 * @since 2.0.0
 */
class LockerProcess implements LockerProcessInterface
{
    /**
     * File extension lock
     */
    const LOCK_EXTENSION = '.lock';

    /**
     * Max execution (locking) time for process (in seconds)
     */
    const MAX_LOCK_TIME = 30;

    /**
     * @var Filesystem
     * @since 2.0.0
     */
    private $filesystem;

    /**
     * @var string
     * @since 2.0.0
     */
    private $lockFilePath;

    /**
     * @var WriteInterface
     * @since 2.0.0
     */
    private $tmpDirectory;

    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @since 2.0.0
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function lockProcess($lockName)
    {
        if ($this->getState()->getMode() == State::MODE_PRODUCTION) {
            return;
        }

        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->lockFilePath = $this->getFilePath($lockName);

        while ($this->isProcessLocked()) {
            usleep(1000);
        }

        $this->tmpDirectory->writeFile($this->lockFilePath, time());
    }

    /**
     * @inheritdoc
     * @throws FileSystemException
     * @since 2.0.0
     */
    public function unlockProcess()
    {
        if ($this->getState()->getMode() == State::MODE_PRODUCTION) {
            return;
        }

        $this->tmpDirectory->delete($this->lockFilePath);
    }

    /**
     * Check whether generation process has already locked
     *
     * @return bool
     * @since 2.0.0
     */
    private function isProcessLocked()
    {
        if ($this->tmpDirectory->isExist($this->lockFilePath)) {
            try {
                $lockTime = (int)$this->tmpDirectory->readFile($this->lockFilePath);
                if ((time() - $lockTime) >= self::MAX_LOCK_TIME) {
                    $this->tmpDirectory->delete($this->lockFilePath);

                    return false;
                }
            } catch (FileSystemException $e) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get name of lock file
     *
     * @param string $name
     * @return string
     * @since 2.0.0
     */
    private function getFilePath($name)
    {
        return DirectoryList::TMP . DIRECTORY_SEPARATOR . $name . self::LOCK_EXTENSION;
    }

    /**
     * @return State
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }
        return $this->state;
    }
}
