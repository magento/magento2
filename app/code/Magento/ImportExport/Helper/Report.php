<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\ValidatorException;
use Magento\ImportExport\Model\Import;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * ImportExport history reports helper
 *
 * @api
 * @since 100.0.2
 */
class Report extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timeZone;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @var ReadInterface
     */
    private $importHistoryDirectory;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timeZone
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\Timezone $timeZone,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->timeZone = $timeZone;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $importHistoryPath = $this->varDirectory->getAbsolutePath('import_history');
        $this->importHistoryDirectory = $filesystem->getDirectoryReadByPath($importHistoryPath);
        parent::__construct($context);
    }

    /**
     * Calculate import time
     *
     * @param string $time
     * @return string
     */
    public function getExecutionTime($time)
    {
        $reportTime = $this->timeZone->date($time);
        $timeDiff = $reportTime->diff($this->timeZone->date());
        return $timeDiff->format('%H:%I:%S');
    }

    /**
     * Get import summary
     *
     * @param \Magento\ImportExport\Model\Import $import
     * @return string
     */
    public function getSummaryStats(\Magento\ImportExport\Model\Import $import)
    {
        $message = __(
            'Created: %1, Updated: %2, Deleted: %3',
            $import->getCreatedItemsCount(),
            $import->getUpdatedItemsCount(),
            $import->getDeletedItemsCount()
        );
        return $message;
    }

    /**
     * Checks imported file exists.
     *
     * @param string $filename
     * @return bool
     */
    public function importFileExists($filename)
    {
        return $this->varDirectory->isFile($this->getFilePath($filename));
    }

    /**
     * Get report file output
     *
     * @param string $filename
     * @return string
     */
    public function getReportOutput($filename)
    {
        return $this->varDirectory->readFile($this->getFilePath($filename));
    }

    /**
     * Get report absolute path.
     *
     * @param string $fileName
     * @return string
     */
    public function getReportAbsolutePath($fileName)
    {
        return $this->varDirectory->getAbsolutePath(Import::IMPORT_HISTORY_DIR . $fileName);
    }

    /**
     * Retrieve report file size
     *
     * @param string $filename
     * @return int|mixed
     */
    public function getReportSize($filename)
    {
        $statResult = $this->varDirectory->stat($this->getFilePath($filename));

        return $statResult['size'] ?? null;
    }

    /**
     * Get file path.
     *
     * @param string $filename
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getFilePath($filename)
    {
        try {
            $filePath = $this->varDirectory->getRelativePath($this->importHistoryDirectory->getAbsolutePath($filename));
        } catch (ValidatorException $e) {
            throw new \InvalidArgumentException('File not found');
        }
        return $filePath;
    }

    /**
     * Get csv delimiter from request.
     *
     * @return string
     * @since 100.2.2
     */
    public function getDelimiter()
    {
        return $this->_request->getParam(Import::FIELD_FIELD_SEPARATOR, ',');
    }
}
