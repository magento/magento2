<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Store\Model\Config;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Magento\Store\Model\Config\Converter */
    protected $_model;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_processorMock;

    protected function setUp()
    {
        $this->_processorMock = $this->getMock(
            'Magento\Store\Model\Config\Processor\Placeholder',
            [],
            [],
            '',
            false
        );
        $this->_model = new \Magento\Store\Model\Config\Converter($this->_processorMock);
    }

    public function testConvert()
    {
        $initial = ['path' => ['to' => ['save' => 'saved value', 'overwrite' => 'old value']]];
        $source = ['path/to/overwrite' => 'overwritten', 'path/to/added' => 'added value'];
        $mergeResult = [
            'path' => [
                'to' => ['save' => 'saved value', 'overwrite' => 'overwritten', 'added' => 'added value'],
            ],
        ];
        $processorResult = '123Value';
        $this->_processorMock->expects(
            $this->once()
        )->method(
            'process'
        )->with(
            $mergeResult
        )->will(
            $this->returnValue($processorResult)
        );

        $this->assertEquals($processorResult, $this->_model->convert($source, $initial));
    }
}
