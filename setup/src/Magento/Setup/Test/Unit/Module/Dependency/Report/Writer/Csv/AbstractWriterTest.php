<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Writer\Csv;

use Magento\Framework\File\Csv;
use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;
use Magento\Setup\Module\Dependency\Report\Writer\Csv\AbstractWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AbstractWriterTest extends TestCase
{
    /**
     * @var AbstractWriter|MockObject
     */
    protected $writer;

    /**
     * @var Csv|MockObject
     */
    protected $csvMock;

    protected function setUp(): void
    {
        $this->csvMock = $this->createMock(Csv::class);

        $this->writer = $this->getMockBuilder(AbstractWriter::class)
            ->setConstructorArgs([$this->csvMock])
            ->onlyMethods(['prepareData'])
            ->getMock();
    }

    public function testWrite()
    {
        $options = ['report_filename' => 'some_filename'];
        $configMock = $this->createMock(ConfigInterface::class);
        $preparedData = ['foo', 'baz', 'bar'];

        $this->writer->expects(
            $this->once()
        )->method(
            'prepareData'
        )->with(
            $configMock
        )->willReturn(
            $preparedData
        );
        $this->csvMock->expects($this->once())->method('saveData')->with($options['report_filename'], $preparedData);

        $this->writer->write($options, $configMock);
    }

    /**
     * @param array $options
     */
    #[DataProvider('dataProviderWrongOptionReportFilename')]
    public function testWriteWithWrongOptionReportFilename($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Writing error: Passed option "report_filename" is wrong.');
        $configMock = $this->createMock(ConfigInterface::class);

        $this->writer->write($options, $configMock);
    }

    /**
     * @return array
     */
    public static function dataProviderWrongOptionReportFilename()
    {
        return [
            [['report_filename' => '']],
            [['there_are_no_report_filename' => 'some_name']]
        ];
    }
}
