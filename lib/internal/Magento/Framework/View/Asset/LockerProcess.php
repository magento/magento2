<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
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
     * @throws FileSystemException
     */
    public function lockProcess($lockName)
    {
        $this->tmpDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->lockFilePath = $this->getFilePath($lockName);

        while ($this->isProcessLocked()) {
            sleep(1);
        }

        $this->tmpDirectory->writeFile($this->lockFilePath, time());

    }

    /**
     * @inheritdoc
     * @throws FileSystemException
     */
    public function unlockProcess()
    {
        $this->tmpDirectory->delete($this->lockFilePath);
    }

    /**
     * Check whether generation process has already locked
     *
     * @return bool
     * @throws FileSystemException
     */
    private function isProcessLocked()
    {
        if ($this->tmpDirectory->isExist($this->lockFilePath)) {
            $lockTime = (int) $this->tmpDirectory->readFile($this->lockFilePath);
            if ((time() - $lockTime) >= self::MAX_LOCK_TIME) {
                $this->tmpDirectory->delete($this->lockFilePath);

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
     */
    private function getFilePath($name)
    {
        return DirectoryList::TMP . DIRECTORY_SEPARATOR . $name . self::LOCK_EXTENSION;
    }
}
