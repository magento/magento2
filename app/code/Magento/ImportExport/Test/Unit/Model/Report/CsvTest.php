<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Test\Unit\Model\Report;

class CsvTest extends \PHPUnit_Framework_TestCase
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

        $this->reportHelperMock = $this->getMock(\Magento\ImportExport\Helper\Report::class, [], [], '', false);

        $this->outputCsvFactoryMock = $this->getMock(
            \Magento\ImportExport\Model\Export\Adapter\CsvFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->outputCsvMock = $this->getMock(\Magento\ImportExport\Model\Export\Adapter\Csv::class, [], [], '', false);
        $this->outputCsvFactoryMock->expects($this->any())->method('create')->willReturn($this->outputCsvMock);

        $this->sourceCsvFactoryMock = $this->getMock(
            \Magento\ImportExport\Model\Import\Source\CsvFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->sourceCsvMock = $this->getMock(\Magento\ImportExport\Model\Import\Source\Csv::class, [], [], '', false);
        $this->sourceCsvMock->expects($this->any())->method('valid')->willReturnOnConsecutiveCalls(true, true, false);
        $this->sourceCsvMock->expects($this->any())->method('current')->willReturnOnConsecutiveCalls(
            [23 => 'first error'],
            [27 => 'second error']
        );
        $this->sourceCsvFactoryMock->expects($this->any())->method('create')->willReturn($this->sourceCsvMock);

        $this->filesystemMock = $this->getMock(\Magento\Framework\Filesystem::class, [], [], '', false);

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
        $errorAggregatorMock = $this->getMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator::class,
            [],
            [],
            '',
            false
        );
        $errorProcessingMock = $this->getMock(
            \Magento\ImportExport\Model\Import\ErrorProcessing::class,
            ['getErrorMessage'],
            [],
            '',
            false
        );
        $errorProcessingMock->expects($this->any())->method('getErrorMessage')->willReturn('some_error_message');
        $errorAggregatorMock->expects($this->any())->method('getErrorByRowNumber')->willReturn([$errorProcessingMock]);
        $this->sourceCsvMock->expects($this->any())->method('getColNames')->willReturn([]);

        $name = $this->csvModel->createReport('some_file_name', $errorAggregatorMock, true);

        $this->assertEquals($name, 'some_file_name_error_report.csv');
    }
}
