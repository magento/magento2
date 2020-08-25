<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ReaderInterface|MockObject
     */
    private $readerMock;

    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->readerMock = $this->getMockForAbstractClass(ReaderInterface::class);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
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
        $config = new Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId,
            $this->serializerMock
        );
        $this->assertEquals($data, $config->get());
        $this->assertEquals('b', $config->get('a'));
        $this->assertNull($config->get('a/b'));
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
        $config = new Data(
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
        $config = new Data(
            $this->readerMock,
            $this->cacheMock,
            $cacheId,
            $this->serializerMock
        );
        $config->reset();
    }
}
