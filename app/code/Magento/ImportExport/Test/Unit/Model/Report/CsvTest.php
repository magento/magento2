<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Report;

use Magento\Framework\Filesystem;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Helper\Report;
use Magento\ImportExport\Model\Export\Adapter\Csv;
use Magento\ImportExport\Model\Export\Adapter\CsvFactory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CsvTest extends TestCase
{
    /**
     * @var Report|MockObject
     */
    protected $reportHelperMock;

    /**
     * @var CsvFactory|MockObject
     */
    protected $outputCsvFactoryMock;

    /**
     * @var Csv|MockObject
     */
    protected $outputCsvMock;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory|MockObject
     */
    protected $sourceCsvFactoryMock;

    /**
     * @var Csv|MockObject
     */
    protected $sourceCsvMock;

    /**
     * @var Filesystem|MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\ImportExport\Model\Report\Csv|ObjectManager
     */
    protected $csvModel;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $testDelimiter = 'some_delimiter';

        $this->reportHelperMock = $this->createMock(Report::class);
        $this->reportHelperMock->expects($this->any())->method('getDelimiter')->willReturn($testDelimiter);

        $this->outputCsvFactoryMock = $this->createPartialMock(
            CsvFactory::class,
            ['create']
        );
        $this->outputCsvMock = $this->createMock(Csv::class);
        $this->outputCsvFactoryMock->expects($this->any())->method('create')->willReturn($this->outputCsvMock);

        $this->sourceCsvFactoryMock = $this->createPartialMock(
            \Magento\ImportExport\Model\Import\Source\CsvFactory::class,
            ['create']
        );
        $this->sourceCsvMock = $this->createMock(\Magento\ImportExport\Model\Import\Source\Csv::class);
        $this->sourceCsvMock->expects($this->any())->method('valid')->willReturnOnConsecutiveCalls(true, true, false);
        $this->sourceCsvMock->expects($this->any())->method('current')->willReturnOnConsecutiveCalls(
            [23 => 'first error'],
            [27 => 'second error']
        );
        $this->sourceCsvFactoryMock
            ->expects($this->any())
            ->method('create')
            ->with(
                [
                    'file' => 'some_file_name',
                    'directory' => null,
                    'delimiter' => $testDelimiter
                ]
            )
            ->willReturn($this->sourceCsvMock);

        $this->filesystemMock = $this->createMock(Filesystem::class);

        $this->csvModel = $objectManager->getObject(
            \Magento\ImportExport\Model\Report\Csv::class,
            [
                'reportHelper' => $this->reportHelperMock,
                'sourceCsvFactory' => $this->sourceCsvFactoryMock,
                'outputCsvFactory' => $this->outputCsvFactoryMock,
                'filesystem' => $this->filesystemMock
            ]
        );
    }

    public function testCreateReport()
    {
        $errorAggregatorMock = $this->createMock(
            ProcessingErrorAggregator::class
        );
        $errorProcessingMock = $this->createPartialMock(
            ProcessingError::class,
            ['getErrorMessage']
        );
        $errorProcessingMock->expects($this->any())->method('getErrorMessage')->willReturn('some_error_message');
        $errorAggregatorMock->expects($this->any())->method('getErrorByRowNumber')->willReturn([$errorProcessingMock]);
        $this->sourceCsvMock->expects($this->any())->method('getColNames')->willReturn([]);

        $name = $this->csvModel->createReport('some_file_name', $errorAggregatorMock, true);

        $this->assertEquals($name, 'some_file_name_error_report.csv');
    }
}
