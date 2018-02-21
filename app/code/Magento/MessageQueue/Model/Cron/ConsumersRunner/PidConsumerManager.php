<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron\ConsumersRunner;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;

/**
 * The class for checking status of process by PID
 */
class PidConsumerManager
{
    /**
     * Extension of PID file
     * @deprecated Moved to the correct responsibility area
     * @see \Magento\MessageQueue\Model\Cron\ConsumersRunner::PID_FILE_EXT
     */
    const PID_FILE_EXT = '.pid';

    /**
     * The class for working with FS
     *
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem $filesystem The class for working with FS
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Checks if consumer process is run by pid from pidFile
     *
     * @param string $pidFilePath The path to file with PID
     * @return bool Returns true if consumer process is run
     * @throws FileSystemException
     */
    public function isRun($pidFilePath)
    {
        $pid = $this->getPid($pidFilePath);
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
     * Returns pid by pidFile path
     *
     * @param string $pidFilePath The path to file with PID
     * @return int Returns pid if pid file exists for consumer else returns 0
     * @throws FileSystemException
     */
    public function getPid($pidFilePath)
    {
        /** @var ReadInterface $directory */
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        if ($directory->isExist($pidFilePath)) {
            return (int) $directory->readFile($pidFilePath);
        }

        return 0;
    }

    /**
     * Saves pid of current process to file
     *
     * @param string $pidFilePath The path to file with pid
     * @throws FileSystemException
     */
    public function savePid($pidFilePath)
    {
        /** @var WriteInterface $directory */
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $directory->writeFile($pidFilePath, function_exists('posix_getpid') ? posix_getpid() : getmypid(), 'w');
    }
}
