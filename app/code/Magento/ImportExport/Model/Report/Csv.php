<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Report;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import;

/**
 * Class Csv create new CSV file and add Error data in additional column
 */
class Csv implements ReportProcessorInterface
{
    const ERROR_REPORT_FILE_SUFFIX = '_error_report';

    const ERROR_REPORT_FILE_EXTENSION = '.csv';

    const REPORT_ERROR_COLUMN_NAME = 'errors';

    /**
     * @var \Magento\ImportExport\Helper\Report
     */
    protected $reportHelper;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory
     */
    protected $sourceCsvFactory;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory
     */
    protected $outputCsvFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     * @param Import\Source\CsvFactory $sourceCsvFactory
     * @param \Magento\ImportExport\Model\Export\Adapter\CsvFactory $outputCsvFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\ImportExport\Helper\Report $reportHelper,
        \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory,
        \Magento\ImportExport\Model\Export\Adapter\CsvFactory $outputCsvFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->reportHelper = $reportHelper;
        $this->sourceCsvFactory = $sourceCsvFactory;
        $this->outputCsvFactory = $outputCsvFactory;
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $originalFileName
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param bool $writeOnlyErrorItems
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createReport(
        $originalFileName,
        ProcessingErrorAggregatorInterface $errorAggregator,
        $writeOnlyErrorItems = false
    ) {
        $sourceCsv = $this->createSourceCsvModel($originalFileName);

        $outputFileName = $this->generateOutputFileName($originalFileName);
        $outputCsv = $this->createOutputCsvModel($outputFileName);

        $columnsName = $sourceCsv->getColNames();
        array_push($columnsName, self::REPORT_ERROR_COLUMN_NAME);
        $outputCsv->setHeaderCols($columnsName);

        foreach ($sourceCsv as $rowNum => $rowData) {
            $errorMessages = $this->retrieveErrorMessagesByRowNumber($rowNum, $errorAggregator);
            if (!$writeOnlyErrorItems || ($writeOnlyErrorItems && $errorMessages)) {
                $rowData[self::REPORT_ERROR_COLUMN_NAME] = $errorMessages;
                $outputCsv->writeRow($rowData);
            }
        }

        return $outputFileName;
    }

    /**
     * @param int $rowNumber
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    public function retrieveErrorMessagesByRowNumber($rowNumber, ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $messages = '';
        foreach ($errorAggregator->getErrorByRowNumber((int)$rowNumber) as $error) {
            $messages .= $error->getErrorMessage() . ',';
        }
        $messages = rtrim($messages, ',');

        if ($messages) {
            $messages = str_pad($messages, 1, '"', STR_PAD_BOTH);
        }

        return $messages;
    }

    /**
     * @param string $sourceFile
     * @return string
     */
    protected function generateOutputFileName($sourceFile)
    {
        $fileName = basename($sourceFile, self::ERROR_REPORT_FILE_EXTENSION);
        return $fileName . self::ERROR_REPORT_FILE_SUFFIX . self::ERROR_REPORT_FILE_EXTENSION;
    }

    /**
     * @param string $sourceFile
     * @return \Magento\ImportExport\Model\Import\Source\Csv
     */
    protected function createSourceCsvModel($sourceFile)
    {
        return $this->sourceCsvFactory->create(
            [
                'file' => $sourceFile,
                'directory' => $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR)
            ]
        );
    }

    /**
     * @param string $outputFileName
     * @return \Magento\ImportExport\Model\Export\Adapter\Csv
     */
    protected function createOutputCsvModel($outputFileName)
    {
        return $this->outputCsvFactory->create(
            [
                'destination' => Import::IMPORT_HISTORY_DIR . $outputFileName,
                'destinationDirectoryCode' => DirectoryList::VAR_DIR,
            ]
        );
    }
}
