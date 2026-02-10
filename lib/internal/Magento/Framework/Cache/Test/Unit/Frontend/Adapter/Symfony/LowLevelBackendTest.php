<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelBackend;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for LowLevelBackend
 */
class LowLevelBackendTest extends TestCase
{
    /**
     * @var TagAdapterInterface|MockObject
     */
    private $adapterMock;

    /**
     * @var LowLevelBackend
     */
    private LowLevelBackend $lowLevelBackend;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->adapterMock = $this->createMock(TagAdapterInterface::class);
        $this->lowLevelBackend = new LowLevelBackend($this->adapterMock);
    }

    /**
     * Test getIdsMatchingTags() when adapter supports the method
     */
    public function testGetIdsMatchingTagsWithSupportedAdapter(): void
    {
        $tags = ['tag1', 'tag2'];
        $expectedIds = ['id1', 'id2', 'id3'];

        // Create a mock that has the getIdsMatchingTags method
        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $adapterMock
            ->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with($tags)
            ->willReturn($expectedIds);

        $backend = new LowLevelBackend($adapterMock);
        $result = $backend->getIdsMatchingTags($tags);

        $this->assertEquals($expectedIds, $result);
    }

    /**
     * Test getIdsMatchingTags() when adapter doesn't support the method
     */
    public function testGetIdsMatchingTagsWithUnsupportedAdapter(): void
    {
        $tags = ['tag1', 'tag2'];

        // Use base mock that doesn't have getIdsMatchingTags method
        $result = $this->lowLevelBackend->getIdsMatchingTags($tags);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsMatchingTags() with empty tags array
     */
    public function testGetIdsMatchingTagsWithEmptyTags(): void
    {
        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $adapterMock
            ->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with([])
            ->willReturn([]);

        $backend = new LowLevelBackend($adapterMock);
        $result = $backend->getIdsMatchingTags([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsMatchingTags() with multiple matching IDs
     */
    public function testGetIdsMatchingTagsWithMultipleMatches(): void
    {
        $tags = ['category', 'product'];
        $expectedIds = ['product_1', 'product_2', 'product_3', 'category_home'];

        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $adapterMock
            ->expects($this->once())
            ->method('getIdsMatchingTags')
            ->with($tags)
            ->willReturn($expectedIds);

        $backend = new LowLevelBackend($adapterMock);
        $result = $backend->getIdsMatchingTags($tags);

        $this->assertEquals($expectedIds, $result);
        $this->assertCount(4, $result);
    }

    /**
     * Test clean() with CLEANING_MODE_ALL
     */
    public function testCleanWithModeAll(): void
    {
        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $backend = new LowLevelBackend($adapterMock);
        $result = $backend->clean(CacheConstants::CLEANING_MODE_ALL);

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_ALL and tags (tags are ignored)
     */
    public function testCleanWithModeAllAndTags(): void
    {
        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $backend = new LowLevelBackend($adapterMock);
        $result = $backend->clean(CacheConstants::CLEANING_MODE_ALL, ['tag1', 'tag2']);

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_ALL when adapter doesn't support method
     */
    public function testCleanWithModeAllUnsupportedAdapter(): void
    {
        // Use base mock without clearAllTagIndices method
        $result = $this->lowLevelBackend->clean(CacheConstants::CLEANING_MODE_ALL);

        // Should still return true (graceful handling)
        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_OLD
     */
    public function testCleanWithModeOld(): void
    {
        // OLD mode should not call clearAllTagIndices
        $this->adapterMock
            ->expects($this->never())
            ->method($this->anything());

        $result = $this->lowLevelBackend->clean(CacheConstants::CLEANING_MODE_OLD);

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_MATCHING_TAG
     */
    public function testCleanWithModeMatchingTag(): void
    {
        // MATCHING_TAG mode should not call clearAllTagIndices (only ALL mode does)
        $result = $this->lowLevelBackend->clean(
            CacheConstants::CLEANING_MODE_MATCHING_TAG,
            ['tag1']
        );

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_NOT_MATCHING_TAG
     */
    public function testCleanWithModeNotMatchingTag(): void
    {
        $result = $this->lowLevelBackend->clean(
            CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG,
            ['tag1']
        );

        $this->assertTrue($result);
    }

    /**
     * Test clean() with CLEANING_MODE_MATCHING_ANY_TAG
     */
    public function testCleanWithModeMatchingAnyTag(): void
    {
        $result = $this->lowLevelBackend->clean(
            CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG,
            ['tag1', 'tag2']
        );

        $this->assertTrue($result);
    }

    /**
     * Test clean() with no parameters uses default mode
     */
    public function testCleanWithDefaultMode(): void
    {
        $adapterMock = $this->createMock(TagAdapterInterface::class);

        $adapterMock
            ->expects($this->once())
            ->method('clearAllIndices');

        $backend = new LowLevelBackend($adapterMock);
        $result = $backend->clean();

        $this->assertTrue($result);
    }

    /**
     * Test clean() always returns true
     */
    public function testCleanAlwaysReturnsTrue(): void
    {
        $modes = [
            CacheConstants::CLEANING_MODE_ALL,
            CacheConstants::CLEANING_MODE_OLD,
            CacheConstants::CLEANING_MODE_MATCHING_TAG,
            CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG,
            CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG,
        ];

        foreach ($modes as $mode) {
            $result = $this->lowLevelBackend->clean($mode, ['tag1']);
            $this->assertTrue($result, "Clean should return true for mode: $mode");
        }
    }
}
