<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\SymfonyAdapters;

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\FilesystemTagAdapter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Unit test for FilesystemTagAdapter
 */
class FilesystemTagAdapterTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface|MockObject
     */
    private $cachePoolMock;

    /**
     * @var string
     */
    private string $tempDir;

    /**
     * @var FilesystemTagAdapter
     */
    private FilesystemTagAdapter $adapter;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePoolMock = $this->createMock(CacheItemPoolInterface::class);
        $this->tempDir = sys_get_temp_dir() . '/magento_fs_tag_adapter_test_' . uniqid();

        $this->adapter = new FilesystemTagAdapter($this->cachePoolMock, $this->tempDir);
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up temp directory
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    /**
     * Recursively remove directory
     *
     * @param string $path
     */
    private function removeDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $fullPath = $path . '/' . $file;
            is_dir($fullPath) ? $this->removeDirectory($fullPath) : unlink($fullPath);
        }
        rmdir($path);
    }

    /**
     * Test constructor creates tag directory
     */
    public function testConstructorCreatesTagDirectory(): void
    {
        $tagDir = $this->tempDir . '/tags/';

        $this->assertDirectoryExists($tagDir);
    }

    /**
     * Test constructor with existing directory
     */
    public function testConstructorWithExistingDirectory(): void
    {
        // Directory already exists from setUp
        $newAdapter = new FilesystemTagAdapter($this->cachePoolMock, $this->tempDir);

        $this->assertInstanceOf(FilesystemTagAdapter::class, $newAdapter);
    }

    /**
     * Test constructor normalizes trailing slash
     */
    public function testConstructorNormalizesTrailingSlash(): void
    {
        $tempDir = sys_get_temp_dir() . '/magento_normalize_test_' . uniqid();

        // Test with trailing slash
        new FilesystemTagAdapter($this->cachePoolMock, $tempDir . '/');
        $this->assertDirectoryExists($tempDir . '/tags/');

        // Test without trailing slash
        new FilesystemTagAdapter($this->cachePoolMock, $tempDir);
        $this->assertDirectoryExists($tempDir . '/tags/');

        // Clean up
        $this->removeDirectory($tempDir);
    }

    /**
     * Test onSave() creates tag index files
     */
    public function testOnSaveCreatesTagIndexFiles(): void
    {
        $id = 'test_id_1';
        $tags = ['config', 'eav'];

        $this->adapter->onSave($id, $tags);

        $tagDir = $this->tempDir . '/tags/';
        $this->assertFileExists($tagDir . 'config');
        $this->assertFileExists($tagDir . 'eav');

        // Verify content
        $configIds = file_get_contents($tagDir . 'config');
        $this->assertStringContainsString($id, $configIds);

        $eavIds = file_get_contents($tagDir . 'eav');
        $this->assertStringContainsString($id, $eavIds);
    }

    /**
     * Test onSave() with empty tags array
     */
    public function testOnSaveWithEmptyTags(): void
    {
        $id = 'test_id';
        $tags = [];

        // Should not crash
        $this->adapter->onSave($id, $tags);

        $this->assertTrue(true);
    }

    /**
     * Test onSave() appends to existing tag files
     */
    public function testOnSaveAppendsToExistingTagFiles(): void
    {
        $id1 = 'test_id_1';
        $id2 = 'test_id_2';
        $tags = ['config'];

        $this->adapter->onSave($id1, $tags);
        $this->adapter->onSave($id2, $tags);

        $tagFile = $this->tempDir . '/tags/config';
        $content = file_get_contents($tagFile);

        $this->assertStringContainsString($id1, $content);
        $this->assertStringContainsString($id2, $content);
    }

    /**
     * Test onSave() doesn't duplicate IDs
     */
    public function testOnSaveDoesntDuplicateIds(): void
    {
        $id = 'test_id_1';
        $tags = ['config'];

        // Save twice
        $this->adapter->onSave($id, $tags);
        $this->adapter->onSave($id, $tags);

        $tagFile = $this->tempDir . '/tags/config';
        $content = file_get_contents($tagFile);
        $lines = explode("\n", trim($content));

        // Should only appear once
        $count = count(array_filter($lines, fn($line) => trim($line) === $id));
        $this->assertEquals(1, $count);
    }

    /**
     * Test onRemove() removes ID from all tag files
     */
    public function testOnRemoveRemovesIdFromTagFiles(): void
    {
        $id1 = 'test_id_1';
        $id2 = 'test_id_2';
        $tags = ['config', 'eav'];

        // Save two items with same tags
        $this->adapter->onSave($id1, $tags);
        $this->adapter->onSave($id2, $tags);

        // Verify both are there
        $configContent = file_get_contents($this->tempDir . '/tags/config');
        $this->assertStringContainsString($id1, $configContent);
        $this->assertStringContainsString($id2, $configContent);

        // Remove first ID
        $this->adapter->onRemove($id1);

        // Verify id1 is gone but id2 remains
        $configContentAfter = file_get_contents($this->tempDir . '/tags/config');
        $this->assertStringNotContainsString($id1, $configContentAfter);
        $this->assertStringContainsString($id2, $configContentAfter);

        $eavContentAfter = file_get_contents($this->tempDir . '/tags/eav');
        $this->assertStringNotContainsString($id1, $eavContentAfter);
        $this->assertStringContainsString($id2, $eavContentAfter);
    }

    /**
     * Test onRemove() deletes tag file when last ID is removed
     */
    public function testOnRemoveDeletesTagFileWhenLastIdRemoved(): void
    {
        $id = 'test_id_1';
        $tags = ['config'];

        // Save single item
        $this->adapter->onSave($id, $tags);

        // Verify tag file exists
        $tagFile = $this->tempDir . '/tags/config';
        $this->assertFileExists($tagFile);

        // Remove the ID
        $this->adapter->onRemove($id);

        // Tag file should be deleted (no more IDs)
        $this->assertFileDoesNotExist($tagFile);
    }

    /**
     * Test onRemove() with non-existent ID doesn't crash
     */
    public function testOnRemoveWithNonExistentId(): void
    {
        // Should not crash
        $this->adapter->onRemove('non_existent_id');

        $this->assertTrue(true);
    }

    /**
     * Test getIdsMatchingTags() returns intersection (AND logic)
     */
    public function testGetIdsMatchingTagsReturnsIntersection(): void
    {
        // Setup: id1 has both tags, id2 has only config, id3 has only eav
        $this->adapter->onSave('id1', ['config', 'eav']);
        $this->adapter->onSave('id2', ['config']);
        $this->adapter->onSave('id3', ['eav']);

        // Get IDs matching BOTH tags
        $result = $this->adapter->getIdsMatchingTags(['config', 'eav']);

        // Only id1 should match (has both tags)
        $this->assertCount(1, $result);
        $this->assertContains('id1', $result);
        $this->assertNotContains('id2', $result);
        $this->assertNotContains('id3', $result);
    }

    /**
     * Test getIdsMatchingTags() with single tag
     */
    public function testGetIdsMatchingTagsWithSingleTag(): void
    {
        $this->adapter->onSave('id1', ['config']);
        $this->adapter->onSave('id2', ['config']);
        $this->adapter->onSave('id3', ['eav']);

        $result = $this->adapter->getIdsMatchingTags(['config']);

        $this->assertCount(2, $result);
        $this->assertContains('id1', $result);
        $this->assertContains('id2', $result);
        $this->assertNotContains('id3', $result);
    }

    /**
     * Test getIdsMatchingTags() with empty array returns empty
     */
    public function testGetIdsMatchingTagsWithEmptyArray(): void
    {
        $result = $this->adapter->getIdsMatchingTags([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsMatchingTags() with non-existent tag returns empty
     */
    public function testGetIdsMatchingTagsWithNonExistentTag(): void
    {
        $this->adapter->onSave('id1', ['config']);

        $result = $this->adapter->getIdsMatchingTags(['nonexistent']);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsMatchingAnyTags() returns union (OR logic)
     */
    public function testGetIdsMatchingAnyTagsReturnsUnion(): void
    {
        $this->adapter->onSave('id1', ['config', 'eav']);
        $this->adapter->onSave('id2', ['config']);
        $this->adapter->onSave('id3', ['eav']);
        $this->adapter->onSave('id4', ['layout']);

        // Get IDs matching ANY of the tags
        $result = $this->adapter->getIdsMatchingAnyTags(['config', 'eav']);

        // id1, id2, id3 should match (have at least one tag)
        $this->assertCount(3, $result);
        $this->assertContains('id1', $result);
        $this->assertContains('id2', $result);
        $this->assertContains('id3', $result);
        $this->assertNotContains('id4', $result);
    }

    /**
     * Test getIdsMatchingAnyTags() with empty array returns empty
     */
    public function testGetIdsMatchingAnyTagsWithEmptyArray(): void
    {
        $result = $this->adapter->getIdsMatchingAnyTags([]);

        $this->assertEquals([], $result);
    }

    /**
     * Test getIdsNotMatchingTags() returns difference
     */
    public function testGetIdsNotMatchingTagsReturnsDifference(): void
    {
        $this->adapter->onSave('id1', ['config']);
        $this->adapter->onSave('id2', ['eav']);
        $this->adapter->onSave('id3', ['layout']);

        // Get IDs NOT matching 'config'
        $result = $this->adapter->getIdsNotMatchingTags(['config']);

        // id2 and id3 should match (don't have 'config' tag)
        $this->assertCount(2, $result);
        $this->assertNotContains('id1', $result);
        $this->assertContains('id2', $result);
        $this->assertContains('id3', $result);
    }

    /**
     * Test getIdsNotMatchingTags() with empty array returns all IDs
     */
    public function testGetIdsNotMatchingTagsWithEmptyArrayReturnsAll(): void
    {
        $this->adapter->onSave('id1', ['config']);
        $this->adapter->onSave('id2', ['eav']);

        // No tags to exclude means all IDs should be returned
        $result = $this->adapter->getIdsNotMatchingTags([]);

        $this->assertCount(2, $result);
        $this->assertContains('id1', $result);
        $this->assertContains('id2', $result);
    }

    /**
     * Test deleteByIds() removes items from cache pool
     */
    public function testDeleteByIdsRemovesItemsFromCachePool(): void
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
     * Test clearAllIndices() removes all tag files
     */
    public function testClearAllIndicesRemovesAllTagFiles(): void
    {
        // Create some tag files
        $this->adapter->onSave('id1', ['config', 'eav']);
        $this->adapter->onSave('id2', ['layout']);

        $tagDir = $this->tempDir . '/tags/';
        $this->assertFileExists($tagDir . 'config');
        $this->assertFileExists($tagDir . 'eav');
        $this->assertFileExists($tagDir . 'layout');

        // Clear all indices
        $this->adapter->clearAllIndices();

        // All tag files should be gone
        $this->assertFileDoesNotExist($tagDir . 'config');
        $this->assertFileDoesNotExist($tagDir . 'eav');
        $this->assertFileDoesNotExist($tagDir . 'layout');
    }

    /**
     * Test clearAllIndices() with empty tag directory doesn't crash
     */
    public function testClearAllIndicesWithEmptyDirectory(): void
    {
        // Should not crash
        $this->adapter->clearAllIndices();

        $this->assertTrue(true);
    }

    /**
     * Test full workflow: save, query, remove
     */
    public function testFullWorkflow(): void
    {
        // Save multiple items with tags
        $this->adapter->onSave('product_1', ['catalog', 'product']);
        $this->adapter->onSave('product_2', ['catalog', 'product']);
        $this->adapter->onSave('category_1', ['catalog', 'category']);
        $this->adapter->onSave('config_1', ['config']);

        // Test MATCHING_TAG (AND logic)
        $productIds = $this->adapter->getIdsMatchingTags(['catalog', 'product']);
        $this->assertCount(2, $productIds);
        $this->assertContains('product_1', $productIds);
        $this->assertContains('product_2', $productIds);

        // Test MATCHING_ANY_TAG (OR logic)
        $catalogIds = $this->adapter->getIdsMatchingAnyTags(['catalog', 'config']);
        $this->assertCount(4, $catalogIds);

        // Test NOT_MATCHING_TAG
        $nonCatalogIds = $this->adapter->getIdsNotMatchingTags(['catalog']);
        $this->assertCount(1, $nonCatalogIds);
        $this->assertContains('config_1', $nonCatalogIds);

        // Remove one item
        $this->adapter->onRemove('product_1');

        // Verify it's removed
        $updatedProductIds = $this->adapter->getIdsMatchingTags(['catalog', 'product']);
        $this->assertCount(1, $updatedProductIds);
        $this->assertContains('product_2', $updatedProductIds);
        $this->assertNotContains('product_1', $updatedProductIds);
    }

    /**
     * Test concurrent saves to same tag
     */
    public function testConcurrentSavesToSameTag(): void
    {
        $tag = 'config';

        // Simulate concurrent saves
        for ($i = 1; $i <= 10; $i++) {
            $this->adapter->onSave("id_$i", [$tag]);
        }

        $ids = $this->adapter->getIdsMatchingTags([$tag]);

        // All 10 IDs should be present
        $this->assertCount(10, $ids);
        for ($i = 1; $i <= 10; $i++) {
            $this->assertContains("id_$i", $ids);
        }
    }

    /**
     * Test tag file content format
     */
    public function testTagFileContentFormat(): void
    {
        $this->adapter->onSave('id1', ['config']);
        $this->adapter->onSave('id2', ['config']);

        $tagFile = $this->tempDir . '/tags/config';
        $content = file_get_contents($tagFile);

        // Should have one ID per line
        $lines = explode("\n", trim($content));
        $this->assertCount(2, $lines);
        $this->assertEquals('id1', trim($lines[0]));
        $this->assertEquals('id2', trim($lines[1]));
    }
}
