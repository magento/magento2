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
class PidConsumerManager
{
    /**
     * Extension of PID file
     */
    const PID_FILE_EXT = '.pid';

    /**
     * The class for working with FS
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * The factory of Write class which creates and writes to file
     *
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * The Magento application specific list of directories
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param Filesystem $filesystem The class for working with FS
     * @param WriteFactory $writeFactory The factory of Write class which creates and writes to file
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
     * @return bool Returns true if consumer process is run
     */
    public function isRun($consumerName)
    {
        $pid = $this->getPid($consumerName);
        if ($pid) {
            if (function_exists('posix_getpgid')) {
                return (bool) posix_getpgid($pid);
            } else {
                return $this->checkIsProcessExists($pid);
            }
        }

        return false;
    }

    /**
     * Checks that process is running
     *
     * If php function exec is not available throws RuntimeException
     * If shell command returns non-zero code and this code is not 1 throws RuntimeException
     *
     * @param int $pid A pid of process
     * @return bool Returns true if consumer process is run
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function checkIsProcessExists($pid)
    {
        if (!function_exists('exec')) {
            throw new \RuntimeException('Function exec is not available');
        }

        exec(escapeshellcmd('ps -p ' . $pid), $output, $code);

        $code = (int) $code;

        switch ($code) {
            case 0:
                return true;
                break;
            case 1:
                return false;
                break;
            default:
                throw new \RuntimeException('Exec returned non-zero code', $code);
                break;
        }
    }

    /**
     * Returns pid by consumer name
     *
     * @param string $consumerName The consumers name
     * @return int|bool Returns pid if pid file exists for consumer else returns false
     */
    public function getPid($consumerName)
    {
        $pidFile = $consumerName . static::PID_FILE_EXT;
        /** @var WriteInterface $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);

        if ($directory->isExist($pidFile)) {
            return (int) $directory->readFile($pidFile);
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
        $file->write(function_exists('posix_getpid') ? posix_getpid() : getmypid());
        $file->close();
    }
}
