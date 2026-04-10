<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
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

    /**
     * @var ConfigWriterInterface|MockObject
     */
    private $configWriterMock;

    protected function setUp(): void
    {
        $this->readerMock = $this->createMock(ReaderInterface::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->configWriterMock = $this->createMock(ConfigWriterInterface::class);
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

    /**
     * When a compiled PHP file exists, data should be loaded from it.
     * Cache backend and XML reader must NOT be called.
     */
    public function testGetConfigFromCompiledFile(): void
    {
        $compiledData = ['compiled' => 'value'];
        $cacheId = 'test';

        $this->cacheMock->expects($this->never())
            ->method('load');
        $this->readerMock->expects($this->never())
            ->method('read');
        $this->serializerMock->expects($this->never())
            ->method('unserialize');

        $config = $this->createCompiledDataConfig(
            $cacheId,
            compiledFileExists: true,
            compiledData: $compiledData,
            configWriter: $this->configWriterMock
        );

        $this->assertEquals($compiledData, $config->get());
        $this->assertEquals('value', $config->get('compiled'));
    }

    /**
     * When configWriter is set and compiled file does not exist and cache misses,
     * data comes from the XML reader and the compiled file is written via configWriter.
     */
    public function testCompiledFileWrittenOnCacheMiss(): void
    {
        $data = ['reader' => 'data'];
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

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with($cacheId, $data);

        $config = $this->createCompiledDataConfig(
            $cacheId,
            compiledFileExists: false,
            compiledData: null,
            configWriter: $this->configWriterMock
        );

        $this->assertEquals($data, $config->get());
    }

    /**
     * When configWriter is set and compiled file does not exist but cache hits,
     * data comes from cache AND the compiled file is still written for next request.
     */
    public function testCompiledFileWrittenOnCacheHit(): void
    {
        $data = ['cached' => 'data'];
        $serializedData = '{"cached":"data"}';
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

        $this->configWriterMock->expects($this->once())
            ->method('write')
            ->with($cacheId, $data);

        $config = $this->createCompiledDataConfig(
            $cacheId,
            compiledFileExists: false,
            compiledData: null,
            configWriter: $this->configWriterMock
        );

        $this->assertEquals($data, $config->get());
    }

    /**
     * When configWriter is null, behavior is identical to original (backward compat).
     * No compilation attempts, cache and reader work as before.
     */
    public function testNoCompilationWithoutConfigWriter(): void
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
    }

    /**
     * When reset() is called, the compiled file should be removed.
     */
    public function testResetRemovesCompiledConfig(): void
    {
        $data = ['a' => 'b'];
        $serializedData = '{"a":"b"}';
        $cacheId = 'test';

        $this->cacheMock->expects($this->once())
            ->method('load')
            ->willReturn($serializedData);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($data);

        $config = $this->createCompiledDataConfig(
            $cacheId,
            compiledFileExists: false,
            compiledData: null,
            configWriter: $this->configWriterMock
        );

        $config->reset();
        $this->assertContains($cacheId, $config->getRemovedCompiledKeys());
    }

    /**
     * Create a Data instance with compiled-file behavior controlled by test parameters.
     *
     * Uses a test subclass to override the compiled-file filesystem checks,
     * since file_exists() and include() cannot be mocked in unit tests.
     */
    private function createCompiledDataConfig(
        string $cacheId,
        bool $compiledFileExists,
        ?array $compiledData,
        ?ConfigWriterInterface $configWriter = null
    ): Data {
        return new TestableCompiledData(
            $this->readerMock,
            $this->cacheMock,
            $cacheId,
            $this->serializerMock,
            $compiledFileExists,
            $compiledData,
            $configWriter
        );
    }
}
