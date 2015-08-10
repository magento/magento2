<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Model\Report;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class Csv create new CSV file and add Error data in additional column
 */
class Csv implements ReportProcessorInterface
{
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
     * @param \Magento\ImportExport\Helper\Report $reportHelper
     */
    public function __construct(
        \Magento\ImportExport\Helper\Report $reportHelper,
        \Magento\ImportExport\Model\Import\Source\CsvFactory $sourceCsvFactory,
        \Magento\ImportExport\Model\Export\Adapter\CsvFactory $outputCsvFactory
    ) {
        $this->reportHelper = $reportHelper;
        $this->sourceCsvFactory = $sourceCsvFactory;
        $this->outputCsvFactory = $outputCsvFactory;
    }

    /**
     * @param string $originalFileName
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return string
     */
    public function createReport($originalFileName, ProcessingErrorAggregatorInterface $errorAggregator)
    {
        $sourceCsv = $this->createSourceCsvModel($originalFileName);
        $outputCsv = $this->createOutputCsvModel();
    }

    /**
     * @param string $originalFileName
     * @return \Magento\ImportExport\Model\Import\Source\Csv
     */
    protected function createSourceCsvModel($originalFileName)
    {
        return $this->sourceCsvFactory->create(
            ['file' => $originalFileName]
        );
    }

    /**
     * @return \Magento\ImportExport\Model\Export\Adapter\Csv
     */
    protected function createOutputCsvModel()
    {
        return $this->outputCsvFactory->create(
            ['destination' => 'test.csv']
        );
    }
}
