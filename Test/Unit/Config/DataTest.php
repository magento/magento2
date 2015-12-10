<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Config;


class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\XmlReader')
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
        $configData = new \Magento\Framework\MessageQueue\Config\Data($this->readerMock, $this->cacheMock);

        $this->assertEquals($expected, $configData->get());
    }
}
