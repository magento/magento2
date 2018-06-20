<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Report;

class CsvTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\ImportExport\Helper\Report|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportHelperMock;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\CsvFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $outputCsvFactoryMock;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\Csv|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $outputCsvMock;

    /**
     * @var \Magento\ImportExport\Model\Import\Source\CsvFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceCsvFactoryMock;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\Csv|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sourceCsvMock;

    /**
     * @var \Magento\Framework\Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filesystemMock;

    /**
     * @var \Magento\ImportExport\Model\Report\Csv|\Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $csvModel;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $testDelimiter = 'some_delimiter';

        $this->reportHelperMock = $this->createMock(\Magento\ImportExport\Helper\Report::class);
        $this->reportHelperMock->expects($this->any())->method('getDelimiter')->willReturn($testDelimiter);

        $this->outputCsvFactoryMock = $this->createPartialMock(
            \Magento\ImportExport\Model\Export\Adapter\CsvFactory::class,
            ['create']
        );
        $this->outputCsvMock = $this->createMock(\Magento\ImportExport\Model\Export\Adapter\Csv::class);
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

        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);

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
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class
        );
        $errorProcessingMock = $this->createPartialMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::class,
            ['getErrorMessage']
        );
        $errorProcessingMock->expects($this->any())->method('getErrorMessage')->willReturn('some_error_message');
        $errorAggregatorMock->expects($this->any())->method('getErrorByRowNumber')->willReturn([$errorProcessingMock]);
        $this->sourceCsvMock->expects($this->any())->method('getColNames')->willReturn([]);

        $name = $this->csvModel->createReport('some_file_name', $errorAggregatorMock, true);

        $this->assertEquals($name, 'some_file_name_error_report.csv');
    }
}
