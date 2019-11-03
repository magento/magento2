<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * This class is responsible for logging dry run SQL`s
 * By default it logs them into filesystem, but it can be extended and you can log them in CLI
 * Current problem with logging output to CLI, is that we have redudant things in CLI output, like modules progress
 */
class DryRunLogger
{
    /**
     * We will run installation or upgrade in Dry Run mode
     */
    const INPUT_KEY_DRY_RUN_MODE = 'dry-run';

    /**
     * File name, where all dry-run SQL`s will be puted
     */
    const FILE_NAME = 'dry-run-installation.log';

    /**
     * Allows to separate 2 different sql statements with this separator
     * Be default is used 2 empty lines
     */
    const LINE_SEPARATOR = "\n\n";

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        DirectoryList $directoryList
    ) {
        $this->fileDriver = $fileDriver;
        $this->directoryList = $directoryList;
    }

    /**
     * Create log directory if it doest not exists
     *
     * @param string $logFolderPath
     * @throws \Exception
     */
    private function assertLogFolderExists($logFolderPath)
    {
        if (!$this->fileDriver->isDirectory($logFolderPath)) {
            $this->fileDriver->createDirectory($logFolderPath);
        }

        if (!$this->fileDriver->isDirectory($logFolderPath)) {
            throw new \Exception(sprintf("Can`t create log directory: %s", $logFolderPath));
        }
    }

    /**
     * @param string $fileName
     * @throws \Exception
     */
    private function assertFileExists($fileName)
    {
        if (!$this->fileDriver->isExists($fileName)) {
            $this->fileDriver->touch($fileName);
        }

        if (!$this->fileDriver->isExists($fileName)) {
            throw new \Exception(sprintf("Can`t create file %s", $fileName));
        }
    }

    /**
     * Make file empty from request to request
     * @throws \Exception
     * @return void
     */
    public function prepareToDryRun()
    {
        if ($this->fileDriver->isExists($this->getLoggerFile())) {
            if (!$this->fileDriver->isWritable($this->getLoggerFile())) {
                throw new \Exception(sprintf('Dry run logger file is not writable'));
            }

            $this->fileDriver->deleteFile($this->getLoggerFile());
            $this->fileDriver->touch($this->getLoggerFile());
        }
    }

    /**
     * Return folder path, where dry run logged file will be placed
     *
     * @return string
     */
    private function getLoggerFolder()
    {
        return $this->directoryList->getPath(DirectoryList::VAR_DIR) .
            DIRECTORY_SEPARATOR . 'log';
    }

    /**
     * Return dry run logger file
     *
     * @return string
     */
    private function getLoggerFile()
    {
        return $this->getLoggerFolder() . DIRECTORY_SEPARATOR . self::FILE_NAME;
    }

    /**
     * Do log of SQL query, 2 different SQL`s will be divided by one empty line
     *
     * @param string $sql
     * @throws \Exception
     * @return void
     */
    public function log($sql)
    {
        $loggerFolder = $this->getLoggerFolder();
        $loggerFile = $this->getLoggerFile();
        $this->assertLogFolderExists($loggerFolder);
        $this->assertFileExists($loggerFile);

        if ($this->fileDriver->isWritable($loggerFile)) {
            $fd = $this->fileDriver->fileOpen($loggerFile, 'a');
            $this->fileDriver->fileWrite($fd, $sql . self::LINE_SEPARATOR);
        } else {
            throw new \Exception(sprintf('Can`t write to file %s', $loggerFile));
        }
    }
}
