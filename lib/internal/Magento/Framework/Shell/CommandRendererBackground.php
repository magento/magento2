<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\OsInfo;

class CommandRendererBackground extends CommandRenderer
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\OsInfo
     */
    protected $osInfo;

    /**
     * @param Filesystem $filesystem
     * @param OsInfo $osInfo
     */
    public function __construct(
        Filesystem $filesystem,
        OsInfo $osInfo
    ) {
        $this->filesystem = $filesystem;
        $this->osInfo = $osInfo;
    }

    /**
     * Render command with arguments
     *
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function render($command, array $arguments = [])
    {
        $command = parent::render($command, $arguments);

        $logFile = '/dev/null';
        if ($groupId = $arguments[2] ?? null) {
            $logDir = $this->filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath();
            $logFile = escapeshellarg($logDir . 'magento.cron.' . $groupId . '.log');
        }

        return $this->osInfo->isWindows() ?
            'start /B "magento background task" ' . $command
            : str_replace('2>&1', ">> $logFile 2>&1 &", $command);
    }
}
