<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelBackend;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelFrontend;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Unit test for LowLevelFrontend
 */
class LowLevelFrontendTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var FrontendInterface|MockObject
     */
    private $symfonyMock;

    /**
     * @var TagAdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var LowLevelFrontend
     */
    private LowLevelFrontend $lowLevelFrontend;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheMock = $this->createMock(CacheItemPoolInterface::class);
        $this->symfonyMock = $this->createMock(FrontendInterface::class);
        $this->adapterMock = $this->createMock(TagAdapterInterface::class);

        $this->lowLevelFrontend = new LowLevelFrontend(
            $this->cacheMock,
            $this->symfonyMock,
            $this->adapterMock,
            'test_prefix_',
            3600
        );
    }

    /**
     * Test constructor sets properties correctly
     */
    public function testConstructorSetsPropertiesCorrectly(): void
    {
        $idPrefix = 'my_prefix_';
        $lifetime = 7200;

        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $this->symfonyMock,
            $this->adapterMock,
            $idPrefix,
            $lifetime
        );

        $this->assertEquals($idPrefix, $frontend->getOption('cache_id_prefix'));
        $this->assertEquals($lifetime, $frontend->getOption('lifetime'));
    }

    /**
     * Test constructor with default lifetime
     */
    public function testConstructorWithDefaultLifetime(): void
    {
        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $this->symfonyMock,
            $this->adapterMock,
            'prefix_'
        );

        $this->assertEquals(7200, $frontend->getOption('lifetime'));
    }

    /**
     * Test getMetadatas() delegates to symfony frontend
     */
    public function testGetMetadatasDelegatesToSymfony(): void
    {
        $expectedMetadata = [
            'expire' => 1234567890,
            'tags' => ['tag1', 'tag2'],
            'mtime' => 1234567000
        ];

        $symfonyMock = $this->getMockBuilder(FrontendInterface::class)
            ->addMethods(['getMetadatas'])
            ->getMockForAbstractClass();

        $symfonyMock
            ->expects($this->once())
            ->method('getMetadatas')
            ->with('test_key')
            ->willReturn($expectedMetadata);

        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $symfonyMock,
            $this->adapterMock,
            'prefix_'
        );

        $result = $frontend->getMetadatas('test_key');

        $this->assertEquals($expectedMetadata, $result);
    }

    /**
     * Test getMetadatas() returns false for missing key
     */
    public function testGetMetadatasReturnsFalseForMissingKey(): void
    {
        $symfonyMock = $this->getMockBuilder(FrontendInterface::class)
            ->addMethods(['getMetadatas'])
            ->getMockForAbstractClass();

        $symfonyMock
            ->expects($this->once())
            ->method('getMetadatas')
            ->with('missing_key')
            ->willReturn(false);

        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $symfonyMock,
            $this->adapterMock,
            'prefix_'
        );

        $result = $frontend->getMetadatas('missing_key');

        $this->assertFalse($result);
    }

    /**
     * Test getOption() returns cache_id_prefix
     */
    public function testGetOptionReturnsCacheIdPrefix(): void
    {
        $result = $this->lowLevelFrontend->getOption('cache_id_prefix');

        $this->assertEquals('test_prefix_', $result);
    }

    /**
     * Test getOption() returns lifetime
     */
    public function testGetOptionReturnsLifetime(): void
    {
        $result = $this->lowLevelFrontend->getOption('lifetime');

        $this->assertEquals(3600, $result);
    }

    /**
     * Test getOption() returns null for unknown option
     */
    public function testGetOptionReturnsNullForUnknownOption(): void
    {
        $result = $this->lowLevelFrontend->getOption('unknown_option');

        $this->assertNull($result);
    }

    /**
     * Test getOption() with various unknown options
     */
    public function testGetOptionWithVariousUnknownOptions(): void
    {
        $options = ['foo', 'bar', 'automatic_serialization', 'write_control'];

        foreach ($options as $option) {
            $result = $this->lowLevelFrontend->getOption($option);
            $this->assertNull($result, "Option '$option' should return null");
        }
    }

    /**
     * Test getIdsMatchingTags() when adapter supports the method
     */
    public function testGetIdsMatchingTagsWithSupportedAdapter(): void
    {
        $tags = ['tag1', 'tag2'];
        $expectedIds = ['id1', 'id2', 'id3'];

        $adapterMock = $this->getMockBuilder(TagAdapterInterface::class)
            ->onlyMethods(['getIdsMatchingTags'])
            ->getMockForAbstractClass();

        $adapterMock
            ->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with($tags)
            ->willReturn($expectedIds);

        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $this->symfonyMock,
            $adapterMock,
            'prefix_'
        );

        $result = $frontend->getIdsMatchingTags($tags);

        $this->assertEquals($expectedIds, $result);
    }

    /**
     * Test getIdsMatchingTags() when adapter doesn't support the method
     */
    public function testGetIdsMatchingTagsWithUnsupportedAdapter(): void
    {
        $tags = ['tag1', 'tag2'];

        // Use base mock that doesn't have getIdsMatchingTags method
        $result = $this->lowLevelFrontend->getIdsMatchingTags($tags);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsMatchingTags() with empty tags array
     */
    public function testGetIdsMatchingTagsWithEmptyTags(): void
    {
        $adapterMock = $this->getMockBuilder(TagAdapterInterface::class)
            ->onlyMethods(['getIdsMatchingTags'])
            ->getMockForAbstractClass();

        $adapterMock
            ->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with([])
            ->willReturn([]);

        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $this->symfonyMock,
            $adapterMock,
            'prefix_'
        );

        $result = $frontend->getIdsMatchingTags([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test getBackend() returns LowLevelBackend instance
     */
    public function testGetBackendReturnsLowLevelBackend(): void
    {
        $backend = $this->lowLevelFrontend->getBackend();

        $this->assertInstanceOf(LowLevelBackend::class, $backend);
    }

    /**
     * Test getBackend() returns same instance on multiple calls
     */
    public function testGetBackendReturnsSameInstance(): void
    {
        $backend1 = $this->lowLevelFrontend->getBackend();
        $backend2 = $this->lowLevelFrontend->getBackend();

        $this->assertSame($backend1, $backend2);
    }

    /**
     * Test __call() delegates to cache
     */
    public function testCallDelegatesToCache(): void
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('hasItem')
            ->with('test_key')
            ->willReturn(true);

        $result = $this->lowLevelFrontend->hasItem('test_key');

        $this->assertTrue($result);
    }

    /**
     * Test __call() with multiple parameters
     */
    public function testCallWithMultipleParameters(): void
    {
        $item = $this->createMock(\Psr\Cache\CacheItemInterface::class);

        $this->cacheMock
            ->expects($this->once())
            ->method('saveDeferred')
            ->with($item)
            ->willReturn(true);

        $result = $this->lowLevelFrontend->saveDeferred($item);

        $this->assertTrue($result);
    }

    /**
     * Test __call() with getItem method
     */
    public function testCallWithGetItem(): void
    {
        $itemMock = $this->createMock(\Psr\Cache\CacheItemInterface::class);

        $this->cacheMock
            ->expects($this->once())
            ->method('getItem')
            ->with('test_key')
            ->willReturn($itemMock);

        $result = $this->lowLevelFrontend->getItem('test_key');

        $this->assertSame($itemMock, $result);
    }

    /**
     * Test __call() with getItems method
     */
    public function testCallWithGetItems(): void
    {
        $keys = ['key1', 'key2', 'key3'];
        $items = [];

        $this->cacheMock
            ->expects($this->once())
            ->method('getItems')
            ->with($keys)
            ->willReturn($items);

        $result = $this->lowLevelFrontend->getItems($keys);

        $this->assertEquals($items, $result);
    }

    /**
     * Test __call() with clear method
     */
    public function testCallWithClear(): void
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('clear')
            ->willReturn(true);

        $result = $this->lowLevelFrontend->clear();

        $this->assertTrue($result);
    }

    /**
     * Test __call() with deleteItem method
     */
    public function testCallWithDeleteItem(): void
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('deleteItem')
            ->with('test_key')
            ->willReturn(true);

        $result = $this->lowLevelFrontend->deleteItem('test_key');

        $this->assertTrue($result);
    }

    /**
     * Test __call() with deleteItems method
     */
    public function testCallWithDeleteItems(): void
    {
        $keys = ['key1', 'key2'];

        $this->cacheMock
            ->expects($this->once())
            ->method('deleteItems')
            ->with($keys)
            ->willReturn(true);

        $result = $this->lowLevelFrontend->deleteItems($keys);

        $this->assertTrue($result);
    }

    /**
     * Test __call() with commit method
     */
    public function testCallWithCommit(): void
    {
        $this->cacheMock
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        $result = $this->lowLevelFrontend->commit();

        $this->assertTrue($result);
    }

    /**
     * Test full workflow: getOption, getMetadatas, getIdsMatchingTags, getBackend
     */
    public function testFullWorkflow(): void
    {
        $symfonyMock = $this->getMockBuilder(FrontendInterface::class)
            ->addMethods(['getMetadatas'])
            ->getMockForAbstractClass();

        $symfonyMock
            ->expects($this->once())
            ->method('getMetadatas')
            ->willReturn(['expire' => 123]);

        $frontend = new LowLevelFrontend(
            $this->cacheMock,
            $symfonyMock,
            $this->adapterMock,
            'workflow_prefix_',
            1800
        );

        // Test getOption
        $this->assertEquals('workflow_prefix_', $frontend->getOption('cache_id_prefix'));
        $this->assertEquals(1800, $frontend->getOption('lifetime'));

        // Test getMetadatas
        $metadata = $frontend->getMetadatas('key');
        $this->assertEquals(['expire' => 123], $metadata);

        // Test getIdsMatchingTags (returns empty for base adapter)
        $ids = $frontend->getIdsMatchingTags(['tag']);
        $this->assertEquals([], $ids);

        // Test getBackend
        $backend = $frontend->getBackend();
        $this->assertInstanceOf(LowLevelBackend::class, $backend);
    }
}
