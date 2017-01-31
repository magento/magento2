<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    protected function setUp()
    {
        $this->_readerMock = $this->getMockBuilder(
            'Magento\Sales\Model\Config\Reader'
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock = $this->getMockBuilder(
            'Magento\Framework\App\Cache\Type\Config'
        )->disableOriginalConstructor()->getMock();
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->_cacheMock->expects($this->any())->method('load')->will($this->returnValue(serialize($expected)));
        $configData = new \Magento\Sales\Model\Config\Data($this->_readerMock, $this->_cacheMock);

        $this->assertEquals($expected, $configData->get());
    }
}
