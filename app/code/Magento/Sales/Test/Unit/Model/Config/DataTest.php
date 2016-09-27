<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Config;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $_cacheMock;

    /**
     * @var \Magento\Framework\Json\JsonInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_readerMock = $this->getMockBuilder(
            \Magento\Sales\Model\Config\Reader::class
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock = $this->getMockBuilder(
            \Magento\Framework\App\Cache\Type\Config::class
        )->disableOriginalConstructor()->getMock();
        $this->jsonMock = $this->getMock(\Magento\Framework\Json\JsonInterface::class);
        $this->objectManager->mockObjectManager([\Magento\Framework\Json\JsonInterface::class => $this->jsonMock]);
    }

    public function tearDown()
    {
        $this->objectManager->restoreObjectManager();
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->_cacheMock->expects($this->once())
            ->method('load');

        $this->jsonMock->method('decode')
            ->willReturn($expected);

        $configData = new \Magento\Sales\Model\Config\Data($this->_readerMock, $this->_cacheMock);

        $this->assertEquals($expected, $configData->get());
    }
}
