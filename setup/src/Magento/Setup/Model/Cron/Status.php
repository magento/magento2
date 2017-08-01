<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

/**
 * Class which provides access to the current status of the Magento setup application.
 *
 * Each job is using this class to share information about its current status.
 * Current status can be seen on the update app web page.
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @since 2.0.0
 */
class Status
{
    /**
     * Path to a file, which content is displayed on the update web page.
     *
     * @var string
     * @since 2.0.0
     */
    protected $statusFilePath;

    /**
     * Path to a log file, which contains all the information displayed on the web page.
     *
     * Note that it can be cleared only manually, it is not cleared by clear() method.
     *
     * @var string
     * @since 2.0.0
     */
    protected $logFilePath;

    /**
     * Path to a flag, which exists when update app is running.
     *
     * @var string
     * @since 2.0.0
     */
    protected $updateInProgressFlagFilePath;

    /**
     * Path to a flag, which exists when error occurred during update app execution.
     *
     * @var string
     * @since 2.0.0
     */
    protected $updateErrorFlagFilePath;

    /**
     * @var Filesystem\Directory\WriteInterface
     * @since 2.0.0
     */
    protected $varReaderWriter;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.1.0
     */
    private $logger;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param SetupLoggerFactory $setupLoggerFactory
     * @param string $statusFilePath
     * @param string $logFilePath
     * @param string $updateInProgressFlagFilePath
     * @param string $updateErrorFlagFilePath
     * @since 2.0.0
     */
    public function __construct(
        Filesystem $filesystem,
        SetupLoggerFactory $setupLoggerFactory,
        $statusFilePath = null,
        $logFilePath = null,
        $updateInProgressFlagFilePath = null,
        $updateErrorFlagFilePath = null
    ) {
        $this->varReaderWriter = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->statusFilePath = $statusFilePath ? $statusFilePath : '.update_status.txt';
        $this->logFilePath = $logFilePath ? $logFilePath : DirectoryList::LOG . '/update.log';
        $this->updateInProgressFlagFilePath = $updateInProgressFlagFilePath
            ? $updateInProgressFlagFilePath
            : '.update_in_progress.flag';
        $this->updateErrorFlagFilePath = $updateErrorFlagFilePath
            ? $updateErrorFlagFilePath
            : '.update_error.flag';
        $this->logger = $setupLoggerFactory->create('setup-cron');
    }

    /**
     * Get status file path
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatusFilePath()
    {
        return $this->varReaderWriter->getAbsolutePath($this->statusFilePath);
    }

    /**
     * Get log file path
     *
     * @return string
     * @since 2.0.0
     */
    public function getLogFilePath()
    {
        return $this->varReaderWriter->getAbsolutePath($this->logFilePath);
    }

    /**
     * Add status update.
     *
     * Add information to a temporary file which is used for status display on a web page and to a permanent status log.
     *
     * @param string $text
     * @param int $severity
     * @param bool $writeToStatusFile
     *
     * @return $this
     * @throws \RuntimeException
     * @since 2.0.0
     */
    public function add($text, $severity = \Psr\Log\LogLevel::INFO, $writeToStatusFile = true)
    {
        $this->logger->log($severity, $text);
        $currentUtcTime = '[' . date('Y-m-d H:i:s T', time()) . '] ';
        $text = $currentUtcTime . $text;
        if ($writeToStatusFile) {
            $this->writeMessageToFile($text, $this->statusFilePath);
        }
        return $this;
    }

    /**
     * Write status information to the file.
     *
     * @param string $text
     * @param string $filePath
     * @return $this
     * @throws \RuntimeException
     * @since 2.0.0
     */
    protected function writeMessageToFile($text, $filePath)
    {
        $isNewFile = !$this->varReaderWriter->isExist($filePath);
        if (!$isNewFile && $this->varReaderWriter->readFile($filePath)) {
            $text = "\n{$text}";
        }
        try {
            $this->varReaderWriter->writeFile($filePath, $text, 'a+');
        } catch (FileSystemException $e) {
            throw new \RuntimeException(sprintf('Cannot add status information to "%s"', $filePath));
        }
        if ($isNewFile) {
            chmod($filePath, 0777);
        }
        return $this;
    }

    /**
     * Check if update application is running.
     *
     * @return bool
     * @since 2.0.0
     */
    public function isUpdateInProgress()
    {
        return $this->varReaderWriter->isExist($this->updateInProgressFlagFilePath);
    }

    /**
     * Set current update app status: true if update is in progress, false otherwise.
     *
     * @param bool $isInProgress
     * @return $this
     * @since 2.0.0
     */
    public function toggleUpdateInProgress($isInProgress = true)
    {
        return $this->setFlagValue($this->updateInProgressFlagFilePath, $isInProgress);
    }

    /**
     * Check if error has occurred during update application execution.
     *
     * @return bool
     * @since 2.0.0
     */
    public function isUpdateError()
    {
        return $this->varReaderWriter->isExist($this->updateErrorFlagFilePath);
    }

    /**
     * Set current update app status: true if error occurred during update app execution, false otherwise.
     *
     * @param bool $isErrorOccurred
     * @return $this
     * @since 2.0.0
     */
    public function toggleUpdateError($isErrorOccurred = true)
    {
        return $this->setFlagValue($this->updateErrorFlagFilePath, $isErrorOccurred);
    }

    /**
     * Create flag in case when value is set to 'true', remove it if value is set to 'false'.
     *
     * @param string $pathToFlagFile
     * @param bool $value
     * @return $this
     * @since 2.0.0
     */
    protected function setFlagValue($pathToFlagFile, $value)
    {
        if ($value) {
            try {
                $this->varReaderWriter->touch($pathToFlagFile);
            } catch (FileSystemException $e) {
                throw new \RuntimeException(sprintf('"%s" cannot be created.', $pathToFlagFile));
            }
        } elseif ($this->varReaderWriter->isExist($pathToFlagFile)) {
            $this->varReaderWriter->delete($pathToFlagFile);
        }
        return $this;
    }
}
