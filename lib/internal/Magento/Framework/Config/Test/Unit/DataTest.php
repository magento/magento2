<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Config\ReaderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readerMock;

    /**
     * @var \Magento\Framework\Config\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->readerMock = $this->createMock(\Magento\Framework\Config\ReaderInterface::class);
        $this->cacheMock = $this->createMock(\Magento\Framework\Config\CacheInterface::class);
        $this->serializerMock = $this->createMock(\Magento\Framework\Serialize\SerializerInterface::class);
    }

    public function testGetConfigNotCached()
    {
        $data = ['a' => 'b'];
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn(false);
        $this->readerMock->expects($this->once())
            ->method('read')
            ->willReturn($data);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($data);
        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId,
            $this->serializerMock
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
        $this->assertEquals(null, $config->get('a/b'));
        $this->assertEquals(33, $config->get('a/b', 33));
    }

    public function testGetConfigCached()
    {
        $data = ['a' => 'b'];
        $serializedData = '{"a":"b"}';
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($serializedData);
        $this->readerMock->expects($this->never())
            ->method('read');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);
        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId,
            $this->serializerMock
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
    }

    public function testReset()
    {
        $serializedData = '';
        $cacheId = 'test';
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn([]);
        $this->cacheMock->expects($this->once())
            ->method('remove')
            ->with($cacheId);
        $config = new \Magento\Framework\Config\Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId,
            $this->serializerMock
        );
        $config->reset();
    }
}
