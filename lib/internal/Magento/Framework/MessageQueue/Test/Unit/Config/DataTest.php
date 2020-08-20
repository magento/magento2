<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MessageQueue\Test\Unit\Config;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\MessageQueue as RemoteServiceReader;
use Magento\Framework\MessageQueue\Config\Data;
use Magento\Framework\MessageQueue\Config\Reader\Env;
use Magento\Framework\MessageQueue\Config\Reader\Xml;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Xml|MockObject
     */
    protected $xmlReaderMock;

    /**
     * @var Env|MockObject
     */
    protected $envReaderMock;

    /**
     * @var RemoteServiceReader|MockObject
     */
    protected $remoteServiceReaderMock;

    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->xmlReaderMock = $this->getMockBuilder(Xml::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->envReaderMock = $this->getMockBuilder(Env::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->remoteServiceReaderMock = $this
            ->getMockBuilder(
                \Magento\Framework\MessageQueue\Code\Generator\Config\RemoteServiceReader\MessageQueue::class
            )->disableOriginalConstructor()
            ->getMock();
        $this->cacheMock = $this->getMockBuilder(CacheInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
    }

    public function testGet()
    {
        $expected = ['someData' => ['someValue', 'someKey' => 'someValue']];
        $this->cacheMock->expects($this->any())
            ->method('load')
            ->willReturn(json_encode($expected));

        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->willReturn($expected);

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
        $objectManager = new ObjectManager($this);
        return $objectManager->getObject(
            Data::class,
            [
                'xmlReader' => $this->xmlReaderMock,
                'cache' => $this->cacheMock,
                'envReader' => $this->envReaderMock,
                'remoteServiceReader' => $this->remoteServiceReaderMock,
                'serializer' => $this->serializerMock,
            ]
        );
    }
}
