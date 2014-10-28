<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Dependency\Report\Writer\Csv;

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
            array('writer' => $this->csvMock)
        );
    }

    public function testWrite()
    {
        $options = array('report_filename' => 'some_filename');
        $configMock = $this->getMock('Magento\Tools\Dependency\Report\Data\ConfigInterface');
        $preparedData = array('foo', 'baz', 'bar');

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
        return array(
            array(array('report_filename' => '')),
            array(array('there_are_no_report_filename' => 'some_name'))
        );
    }
}
