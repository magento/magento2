<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\GenericTagAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Unit test for GenericTagAdapter
 */
class GenericTagAdapterTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface|MockObject
     */
    private $cachePoolMock;

    /**
     * @var GenericTagAdapter
     */
    private GenericTagAdapter $adapter;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        $this->adapter = new GenericTagAdapter($this->cachePoolMock, false);
    }

    /**
     * Test constructor with page cache enabled
     */
    public function testConstructorWithPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);

        $this->assertInstanceOf(GenericTagAdapter::class, $adapter);
    }

    /**
     * Test constructor with page cache disabled
     */
    public function testConstructorWithoutPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, false);

        $this->assertInstanceOf(GenericTagAdapter::class, $adapter);
    }

    /**
     * Test generateNamespaceTag() with single tag
     */
    public function testGenerateNamespaceTagWithSingleTag(): void
    {
        $tags = ['config'];
        $result = $this->adapter->generateNamespaceTag($tags);

        $this->assertEquals('NS_config', $result);
    }

    /**
     * Test generateNamespaceTag() with multiple tags
     */
    public function testGenerateNamespaceTagWithMultipleTags(): void
    {
        $tags = ['config', 'eav', 'block_html'];
        $result = $this->adapter->generateNamespaceTag($tags);

        // Should be sorted alphabetically
        $this->assertEquals('NS_block_html|config|eav', $result);
    }

    /**
     * Test generateNamespaceTag() with duplicate tags
     */
    public function testGenerateNamespaceTagWithDuplicateTags(): void
    {
        $tags = ['config', 'config', 'eav'];
        $result = $this->adapter->generateNamespaceTag($tags);

        // Duplicates should be removed
        $this->assertEquals('NS_config|eav', $result);
    }

    /**
     * Test generateNamespaceTag() with unsorted tags
     */
    public function testGenerateNamespaceTagWithUnsortedTags(): void
    {
        $tags = ['zend', 'config', 'apple'];
        $result = $this->adapter->generateNamespaceTag($tags);

        // Should be sorted
        $this->assertEquals('NS_apple|config|zend', $result);
    }

    /**
     * Test generateNamespaceTag() with empty array
     */
    public function testGenerateNamespaceTagWithEmptyArray(): void
    {
        $tags = [];
        $result = $this->adapter->generateNamespaceTag($tags);

        $this->assertEquals('NS_', $result);
    }

    /**
     * Test generateNamespaceTag() with numeric keys
     */
    public function testGenerateNamespaceTagWithNumericKeys(): void
    {
        $tags = [2 => 'config', 5 => 'eav', 1 => 'block'];
        $result = $this->adapter->generateNamespaceTag($tags);

        // Should use array_values to normalize keys and sort
        $this->assertEquals('NS_block|config|eav', $result);
    }

    /**
     * Test getIdsMatchingTags() returns empty array
     */
    public function testGetIdsMatchingTagsReturnsEmpty(): void
    {
        $tags = ['config', 'eav'];
        $result = $this->adapter->getIdsMatchingTags($tags);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsMatchingAnyTags() returns empty array
     */
    public function testGetIdsMatchingAnyTagsReturnsEmpty(): void
    {
        $tags = ['config', 'eav'];
        $result = $this->adapter->getIdsMatchingAnyTags($tags);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsNotMatchingTags() returns empty array
     */
    public function testGetIdsNotMatchingTagsReturnsEmpty(): void
    {
        $tags = ['config', 'eav'];
        $result = $this->adapter->getIdsNotMatchingTags($tags);

        $this->assertEquals([], $result);
    }

    /**
     * Test deleteByIds() with empty array returns true
     */
    public function testDeleteByIdsWithEmptyArrayReturnsTrue(): void
    {
        $this->cachePoolMock
            ->expects($this->never())
            ->method('deleteItems');

        $result = $this->adapter->deleteByIds([]);

        $this->assertTrue($result);
    }

    /**
     * Test deleteByIds() delegates to cache pool
     */
    public function testDeleteByIdsDelegatesToCachePool(): void
    {
        $ids = ['id1', 'id2', 'id3'];

        $this->cachePoolMock
            ->expects($this->once())
            ->method('deleteItems')
            ->with($ids)
            ->willReturn(true);

        $result = $this->adapter->deleteByIds($ids);

        $this->assertTrue($result);
    }

    /**
     * Test deleteByIds() returns false on failure
     */
    public function testDeleteByIdsReturnsFalseOnFailure(): void
    {
        $ids = ['id1', 'id2'];

        $this->cachePoolMock
            ->expects($this->once())
            ->method('deleteItems')
            ->with($ids)
            ->willReturn(false);

        $result = $this->adapter->deleteByIds($ids);

        $this->assertFalse($result);
    }

    /**
     * Test onSave() is a no-op (doesn't crash)
     */
    public function testOnSaveIsNoOp(): void
    {
        // Should not throw any exceptions
        $this->adapter->onSave('test_id', ['tag1', 'tag2']);

        // No assertions needed - just verify it doesn't crash
        $this->assertTrue(true);
    }

    /**
     * Test onSave() with empty tags
     */
    public function testOnSaveWithEmptyTags(): void
    {
        $this->adapter->onSave('test_id', []);

        $this->assertTrue(true);
    }

    /**
     * Test onRemove() is a no-op (doesn't crash)
     */
    public function testOnRemoveIsNoOp(): void
    {
        // Should not throw any exceptions
        $this->adapter->onRemove('test_id');

        // No assertions needed - just verify it doesn't crash
        $this->assertTrue(true);
    }

    /**
     * Test clearAllIndices() is a no-op (doesn't crash)
     */
    public function testClearAllIndicesIsNoOp(): void
    {
        // Should not throw any exceptions
        $this->adapter->clearAllIndices();

        // No assertions needed - just verify it doesn't crash
        $this->assertTrue(true);
    }

    /**
     * Test getTagsForSave() with no tags
     */
    public function testGetTagsForSaveWithNoTags(): void
    {
        $result = $this->adapter->getTagsForSave([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test getTagsForSave() with single tag (application cache)
     */
    public function testGetTagsForSaveWithSingleTagApplicationCache(): void
    {
        $tags = ['config'];
        $result = $this->adapter->getTagsForSave($tags);

        // Application cache doesn't use namespace tags
        $this->assertEquals(['config'], $result);
    }

    /**
     * Test getTagsForSave() with multiple tags (application cache)
     */
    public function testGetTagsForSaveWithMultipleTagsApplicationCache(): void
    {
        $tags = ['config', 'eav'];
        $result = $this->adapter->getTagsForSave($tags);

        // Application cache doesn't use namespace tags
        $this->assertEquals(['config', 'eav'], $result);
    }

    /**
     * Test getTagsForSave() with two tags (page cache)
     */
    public function testGetTagsForSaveWithTwoTagsPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);
        $tags = ['config', 'eav'];
        $result = $adapter->getTagsForSave($tags);

        // Page cache with 2-4 tags should add namespace tag
        $this->assertContains('config', $result);
        $this->assertContains('eav', $result);
        $this->assertContains('NS_config|eav', $result);
        $this->assertCount(3, $result);
    }

    /**
     * Test getTagsForSave() with three tags (page cache)
     */
    public function testGetTagsForSaveWithThreeTagsPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);
        $tags = ['config', 'eav', 'block_html'];
        $result = $adapter->getTagsForSave($tags);

        // Should have original 3 tags + 1 namespace tag
        $this->assertCount(4, $result);
        $this->assertContains('NS_block_html|config|eav', $result);
    }

    /**
     * Test getTagsForSave() with four tags (page cache)
     */
    public function testGetTagsForSaveWithFourTagsPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);
        $tags = ['config', 'eav', 'block_html', 'layout'];
        $result = $adapter->getTagsForSave($tags);

        // Should have original 4 tags + 1 namespace tag
        $this->assertCount(5, $result);
        $this->assertContains('NS_block_html|config|eav|layout', $result);
    }

    /**
     * Test getTagsForSave() with five tags (page cache) - exceeds max
     */
    public function testGetTagsForSaveWithFiveTagsPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);
        $tags = ['config', 'eav', 'block_html', 'layout', 'translate'];
        $result = $adapter->getTagsForSave($tags);

        // Exceeds MAX_TAGS_FOR_NAMESPACE (4), so no namespace tag added
        $this->assertEquals($tags, $result);
        $this->assertCount(5, $result);
    }

    /**
     * Test getTagsForSave() with single tag (page cache)
     */
    public function testGetTagsForSaveWithSingleTagPageCache(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);
        $tags = ['config'];
        $result = $adapter->getTagsForSave($tags);

        // Single tag doesn't get namespace tag (needs 2+ tags)
        $this->assertEquals(['config'], $result);
    }

    /**
     * Test getTagsForSave() removes duplicates
     */
    public function testGetTagsForSaveRemovesDuplicates(): void
    {
        $adapter = new GenericTagAdapter($this->cachePoolMock, true);
        $tags = ['config', 'config', 'eav'];
        $result = $adapter->getTagsForSave($tags);

        // Should deduplicate
        $this->assertContains('config', $result);
        $this->assertContains('eav', $result);
        $this->assertContains('NS_config|eav', $result);
        $this->assertCount(3, $result);
    }
}
