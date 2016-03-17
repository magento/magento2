<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Test\Unit\Config;

use Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\MessageQueue as RemoteServiceReader;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Xml|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $xmlReaderMock;

    /**
     * @var \Magento\Framework\MessageQueue\Config\Reader\Env|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $envReaderMock;

    /**
     * @var RemoteServiceReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $remoteServiceReaderMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    protected function setUp()
    {
        $this->xmlReaderMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\Xml')
            ->disableOriginalConstructor()
            ->getMock();
        $this->envReaderMock = $this->getMockBuilder('Magento\Framework\MessageQueue\Config\Reader\Env')
            ->disableOriginalConstructor()
            ->getMock();
        $this->remoteServiceReaderMock = $this
            ->getMockBuilder('Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\MessageQueue')
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
        $this->remoteServiceReaderMock->expects($this->any())->method('read')->willReturn([]);
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
                'envReader' => $this->envReaderMock,
                'remoteServiceReader' => $this->remoteServiceReaderMock
            ]
        );
    }
}
