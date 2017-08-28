<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron\ConsumersRunner;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Filesystem\DriverPool;

/**
 * The class for checking status of process by PID
 */
class Pid
{
    /**
     * Extension of PID file
     */
    const PID_FILE_EXT = '.pid';

    /**
     * @var Filesystem The class for working with FS
     */
    private $filesystem;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * @var DirectoryList The Magento application specific list of directories
     */
    private $directoryList;

    /**
     * @param Filesystem $filesystem The class for working with FS
     * @param WriteFactory $writeFactory
     * @param DirectoryList $directoryList The Magento application specific list of directories
     */
    public function __construct(
        Filesystem $filesystem,
        WriteFactory $writeFactory,
        DirectoryList $directoryList
    ) {
        $this->filesystem = $filesystem;
        $this->writeFactory = $writeFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * Checks if consumer process is run by consumers name
     *
     * @param string $consumerName The consumers name
     * @return bool Return true if consumer process is run
     */
    public function isRun($consumerName)
    {
        $pidFile = $consumerName . static::PID_FILE_EXT;
        /** @var WriteInterface $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        if ($directory->isExist($pidFile)) {
            $pid = (int) $directory->readFile($pidFile);
            return (bool) posix_getpgid($pid);
        }

        return false;
    }

    /**
     * Returns path to file with PID by consumers name
     *
     * @param string $consumerName The consumers name
     * @return string The path to file with PID
     */
    public function getPidFilePath($consumerName)
    {
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/' . $consumerName . static::PID_FILE_EXT;
    }

    /**
     * Saves pid of current process to file
     *
     * @param string $pidFilePath The path to file with pid
     */
    public function savePid($pidFilePath)
    {
        $file = $this->writeFactory->create($pidFilePath, DriverPool::FILE, 'w');
        $file->write(posix_getpid());
        $file->close();
    }
}
