<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Dependency\Report\Writer\Csv;

use Magento\Framework\File\Csv;
use Magento\Setup\Module\Dependency\Report\Data\ConfigInterface;
use Magento\Setup\Module\Dependency\Report\Writer\Csv\AbstractWriter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

        $this->writer = $this->getMockForAbstractClass(
            AbstractWriter::class,
            ['writer' => $this->csvMock]
        );
    }

    public function testWrite()
    {
        $options = ['report_filename' => 'some_filename'];
        $configMock = $this->getMockForAbstractClass(ConfigInterface::class);
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
     * @dataProvider dataProviderWrongOptionReportFilename
     */
    public function testWriteWithWrongOptionReportFilename($options)
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Writing error: Passed option "report_filename" is wrong.');
        $configMock = $this->getMockForAbstractClass(ConfigInterface::class);

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
