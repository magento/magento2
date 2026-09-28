<?php

/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Cron\Shell;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\OsInfo;
use Magento\Framework\Shell\CommandRenderer;

class CommandRendererBackground extends CommandRenderer
{
    /**
     * @param Filesystem $filesystem
     * @param OsInfo $osInfo
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly OsInfo $osInfo,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function render($command, array $arguments = []): string
    {
        $command = parent::render($command, $arguments);

        $logFile = '/dev/null';
        if ($groupId = $arguments[2] ?? null) {
            $logDir = $this->filesystem->getDirectoryRead(DirectoryList::LOG)->getAbsolutePath();
            // @phpcs:ignore Magento2.Functions.DiscouragedFunction.Discouraged
            $logFile = escapeshellarg($logDir . 'magento.cron.' . $groupId . '.log');
        }

        return $this->osInfo->isWindows() ?
            'start /B "magento background task" ' . $command
            : str_replace('2>&1', ">> $logFile 2>&1 &", $command);
    }
}
