<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Config;


class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\XmlReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $xmlReaderMock;

    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\EnvReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $envReaderMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $this->xmlReaderMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\XmlReader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->envReaderMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\EnvReader')
            ->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder('Magento\Framework\Config\CacheInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->will($this->returnValue(serialize($expected)));
        $this->envReaderMock->expects($this->any())->method('read')->willReturn([]);
        $this->assertEquals($expected, $this->getModel()->get());
    }

    /**
     * Return Config Data Object
     *
     * @return \Magento\Framework\MessageQueue\Config\Data
     */
    private function getModel()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        return $objectManager->getObject(
            'Magento\Framework\MessageQueue\Config\Data',
            [
                'xmlReader' => $this->xmlReaderMock,
                'cache' => $this->cacheMock,
                'envReader' => $this->envReaderMock
            ]
        );
    }
}
