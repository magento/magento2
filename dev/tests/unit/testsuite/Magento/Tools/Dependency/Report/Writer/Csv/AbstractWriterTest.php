<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Dependency\Report\Writer\Csv;

class AbstractWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\Report\Writer\Csv\AbstractWriter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $writer;

    /**
     * @var \Magento\Framework\File\Csv|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $csvMock;

    protected function setUp()
    {
        $this->csvMock = $this->getMock('Magento\Framework\File\Csv');

        $this->writer = $this->getMockForAbstractClass(
            'Magento\Tools\Dependency\Report\Writer\Csv\AbstractWriter',
            ['writer' => $this->csvMock]
        );
    }

    public function testWrite()
    {
        $options = ['report_filename' => 'some_filename'];
        $configMock = $this->getMock('Magento\Tools\Dependency\Report\Data\ConfigInterface');
        $preparedData = ['foo', 'baz', 'bar'];

        $this->writer->expects(
            $this->once()
        )->method(
            'prepareData'
        )->with(
            $configMock
        )->will(
            $this->returnValue($preparedData)
        );
        $this->csvMock->expects($this->once())->method('saveData')->with($options['report_filename'], $preparedData);

        $this->writer->write($options, $configMock);
    }

    /**
     * @param array $options
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Writing error: Passed option "report_filename" is wrong.
     * @dataProvider dataProviderWrongOptionReportFilename
     */
    public function testWriteWithWrongOptionReportFilename($options)
    {
        $configMock = $this->getMock('Magento\Tools\Dependency\Report\Data\ConfigInterface');

        $this->writer->write($options, $configMock);
    }

    /**
     * @return array
     */
    public function dataProviderWrongOptionReportFilename()
    {
        return [
            [['report_filename' => '']],
            [['there_are_no_report_filename' => 'some_name']]
        ];
    }
}
