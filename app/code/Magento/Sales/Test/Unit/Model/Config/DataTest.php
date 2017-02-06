<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
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
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_readerMock = $this->getMockBuilder(
            \Magento\Sales\Model\Config\Reader::class
        )->disableOriginalConstructor()->getMock();
        $this->_cacheMock = $this->getMockBuilder(
            \Magento\Framework\App\Cache\Type\Config::class
        )->disableOriginalConstructor()->getMock();
        $this->serializerMock = $this->getMock(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->_cacheMock->expects($this->once())
            ->method('load');

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expected);

        $configData = $this->objectManager->getObject(
            \Magento\Sales\Model\Config\Data::class,
            [
                'reader' => $this->_readerMock,
                'cache' => $this->_cacheMock,
                'serializer' => $this->serializerMock,
            ]
        );

        $this->assertEquals($expected, $configData->get());
    }
}
