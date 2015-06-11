<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import;

/**
 * ImportExport history reports helper
 */
class Report extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->dateTime = $dateTime;
        $this->filesystem = $filesystem;
        $this->varDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
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
        $reportTime = new \DateTime($this->dateTime->date($time));
        $timeDiff = $reportTime->diff(new \DateTime($this->dateTime->date()));
        return $timeDiff->format('%H:%M:%S');
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
            'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
            $import->getProcessedRowsCount(),
            $import->getProcessedEntitiesCount(),
            $import->getInvalidRowsCount(),
            $import->getErrorsCount()
        );
        return $message;
    }

    /**
     * Check if import process failed is failed by maximum execution time
     *
     * @param string $time
     * @return bool
     */
    public function isFailed($time)
    {
        $isFailed = false;
        if ($this->getTimeDiff($time) > History::MAX_IMPORT_EXECUTION_TIME) {
            $isFailed = true;
        }
        return $isFailed;
    }

    /**
     * Get time difference
     *
     * @param string $time
     * @return int
     */
    protected function getTimeDiff($time)
    {
        return $this->dateTime->timestamp($time) - $this->dateTime->timestamp();
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
     * Retrieve report file size
     *
     * @param string $filename
     * @return int|mixed
     */
    public function getReportSize($filename)
    {
        return $this->varDirectory->stat($this->getFilePath($filename))['size'];
    }

    /**
     * Get file path.
     *
     * @param string $filename
     * @return string
     */
    protected function getFilePath($filename)
    {
        return $this->varDirectory->getRelativePath(Import::IMPORT_HISTORY_DIR . $filename);
    }
}
