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
     */
    private $filesystem;

    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var WriteInterface
     */
    private $tmpDirectory;

    /**
     * @var State
     */
    private $state;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @inheritdoc
     */
    public function lockProcess($lockName)
    {
        if ($this->getState()->getMode() === State::MODE_PRODUCTION || PHP_SAPI === 'cli') {
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
     *
     * @throws FileSystemException
     */
    public function unlockProcess()
    {
        if ($this->getState()->getMode() === State::MODE_PRODUCTION || PHP_SAPI === 'cli') {
            return;
        }

        $this->tmpDirectory->delete($this->lockFilePath);
    }

    /**
     * Check whether generation process has already locked
     *
     * @return bool
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
     * Get path to lock file
     *
     * @param string $name
     *
     * @return string
     */
    private function getFilePath($name)
    {
        return DirectoryList::TMP . DIRECTORY_SEPARATOR . $name . self::LOCK_EXTENSION;
    }

    /**
     * Get State object
     *
     * @return State
     *
     * @deprecated 100.1.1
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }
        return $this->state;
    }
}
