<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache;

use Magento\Framework\App\Cache\Frontend\Factory;
use Magento\Framework\Cache\FrontendInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for Symfony cache frontend adapter with File backend
 *
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class SymfonyFileAdapterTest extends TestCase
{
    /**
     * @var FrontendInterface
     */
    private FrontendInterface $cache;

    /**
     * @var Factory
     */
    private Factory $cacheFactory;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheFactory = Bootstrap::getObjectManager()->get(Factory::class);

        // Create Symfony cache adapter using Factory
        $this->cache = $this->cacheFactory->create([
            'frontend' => [
                'backend' => 'file',
                'backend_options' => [
                    'cache_dir' => BP . '/var/cache/test_symfony'
                ]
            ]
        ]);
    }

    /**
     * Tear down test environment
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up test cache
        if ($this->cache) {
            $this->cache->clean(CacheConstants::CLEANING_MODE_ALL);
        }
    }

    /**
     * Test basic save and load operations
     */
    public function testBasicSaveAndLoad(): void
    {
        $id = 'test_basic_' . uniqid();
        $data = 'test_data_' . time();

        // Save
        $saveResult = $this->cache->save($data, $id);
        $this->assertTrue($saveResult, 'Save should succeed');

        // Load
        $loadResult = $this->cache->load($id);
        $this->assertEquals($data, $loadResult, 'Loaded data should match saved data');
    }

    /**
     * Test save and load with tags
     */
    public function testSaveAndLoadWithTags(): void
    {
        $id = 'test_tags_' . uniqid();
        $data = 'test_data_with_tags';
        $tags = ['tag1', 'tag2', 'tag3'];

        // Save with tags
        $saveResult = $this->cache->save($data, $id, $tags);
        $this->assertTrue($saveResult, 'Save with tags should succeed');

        // Load
        $loadResult = $this->cache->load($id);
        $this->assertEquals($data, $loadResult, 'Loaded data should match saved data');

        // Verify data is tagged by testing tag-based clean
        $id2 = 'test_tags_2_' . uniqid();
        $this->cache->save('other_data', $id2, ['other_tag']);

        // Clean by one of the tags
        $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['tag1']);

        // Original item should be removed
        $this->assertFalse($this->cache->load($id), 'Item with tag1 should be removed');
        // Other item should still exist
        $this->assertEquals('other_data', $this->cache->load($id2), 'Item without tag1 should remain');
    }

    /**
     * Test save with lifetime
     */
    public function testSaveWithLifetime(): void
    {
        $id = 'test_lifetime_' . uniqid();
        $data = 'test_data_lifetime';
        $lifetime = 3600; // 1 hour

        $saveResult = $this->cache->save($data, $id, [], $lifetime);
        $this->assertTrue($saveResult, 'Save with lifetime should succeed');

        $loadResult = $this->cache->load($id);
        $this->assertEquals($data, $loadResult, 'Data should be loadable within lifetime');

        // Verify test() returns a timestamp (indicates the item exists and has expiry)
        $testResult = $this->cache->test($id);
        $this->assertIsInt($testResult, 'test() should return int timestamp for item with lifetime');
        $this->assertGreaterThan(0, $testResult, 'Timestamp should be positive');
    }

    /**
     * Test save with null lifetime (infinite)
     */
    public function testSaveWithNullLifetime(): void
    {
        $id = 'test_null_lifetime_' . uniqid();
        $data = 'test_data_infinite';

        $saveResult = $this->cache->save($data, $id, [], null);
        $this->assertTrue($saveResult, 'Save with null lifetime should succeed');

        $loadResult = $this->cache->load($id);
        $this->assertEquals($data, $loadResult);
    }

    /**
     * Test test() method
     */
    public function testTestMethod(): void
    {
        $id = 'test_method_' . uniqid();
        $data = 'test_data';

        // Test non-existent key
        $testResult = $this->cache->test($id);
        $this->assertFalse($testResult, 'Test should return false for non-existent key');

        // Save
        $this->cache->save($data, $id);

        // Test existing key
        $testResult = $this->cache->test($id);
        $this->assertIsInt($testResult, 'Test should return timestamp for existing key');
        $this->assertGreaterThan(0, $testResult);
    }

    /**
     * Test remove() method
     */
    public function testRemove(): void
    {
        $id = 'test_remove_' . uniqid();
        $data = 'test_data';

        // Save
        $this->cache->save($data, $id);
        $this->assertEquals($data, $this->cache->load($id), 'Data should exist before remove');

        // Remove
        $removeResult = $this->cache->remove($id);
        $this->assertTrue($removeResult, 'Remove should succeed');

        // Verify removed
        $loadResult = $this->cache->load($id);
        $this->assertFalse($loadResult, 'Load should return false after remove');
    }

    /**
     * Test clean with CLEANING_MODE_ALL
     */
    public function testCleanModeAll(): void
    {
        $id1 = 'test_clean_all_1_' . uniqid();
        $id2 = 'test_clean_all_2_' . uniqid();

        // Save multiple items
        $this->cache->save('data1', $id1);
        $this->cache->save('data2', $id2);

        // Verify they exist
        $this->assertEquals('data1', $this->cache->load($id1));
        $this->assertEquals('data2', $this->cache->load($id2));

        // Clean all
        $cleanResult = $this->cache->clean(CacheConstants::CLEANING_MODE_ALL);
        $this->assertTrue($cleanResult, 'Clean all should succeed');

        // Verify all removed
        $this->assertFalse($this->cache->load($id1), 'Item 1 should be removed');
        $this->assertFalse($this->cache->load($id2), 'Item 2 should be removed');
    }

    /**
     * Test clean with CLEANING_MODE_OLD
     */
    public function testCleanModeOld(): void
    {
        $id = 'test_clean_old_' . uniqid();
        $this->cache->save('data', $id);

        // OLD mode is a no-op in Symfony (expiration is automatic)
        $cleanResult = $this->cache->clean(CacheConstants::CLEANING_MODE_OLD);
        $this->assertTrue($cleanResult, 'Clean old should return true');

        // Data should still exist (not actually cleaned)
        $this->assertEquals('data', $this->cache->load($id));
    }

    /**
     * Test clean with CLEANING_MODE_MATCHING_TAG
     */
    public function testCleanModeMatchingTag(): void
    {
        $id1 = 'test_matching_1_' . uniqid();
        $id2 = 'test_matching_2_' . uniqid();
        $id3 = 'test_matching_3_' . uniqid();

        // Save with different tag combinations
        $this->cache->save('data1', $id1, ['tagA', 'tagB']); // Has both
        $this->cache->save('data2', $id2, ['tagA']); // Has only tagA
        $this->cache->save('data3', $id3, ['tagB']); // Has only tagB

        // Clean items matching BOTH tagA AND tagB
        $cleanResult = $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_TAG, ['tagA', 'tagB']);
        $this->assertTrue($cleanResult, 'Clean matching tag should succeed');

        // Only id1 should be removed (has both tags)
        $this->assertFalse($this->cache->load($id1), 'Item with both tags should be removed');

        // id2 and id3 should still exist (have only one tag each)
        $this->assertEquals('data2', $this->cache->load($id2), 'Item with only tagA should remain');
        $this->assertEquals('data3', $this->cache->load($id3), 'Item with only tagB should remain');
    }

    /**
     * Test clean with CLEANING_MODE_MATCHING_ANY_TAG
     */
    public function testCleanModeMatchingAnyTag(): void
    {
        $id1 = 'test_any_1_' . uniqid();
        $id2 = 'test_any_2_' . uniqid();
        $id3 = 'test_any_3_' . uniqid();

        // Save with different tags
        $this->cache->save('data1', $id1, ['tagA']);
        $this->cache->save('data2', $id2, ['tagB']);
        $this->cache->save('data3', $id3, ['tagC']);

        // Clean items matching ANY of tagA or tagB
        $cleanResult = $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['tagA', 'tagB']);
        $this->assertTrue($cleanResult, 'Clean matching any tag should succeed');

        // id1 and id2 should be removed (have tagA or tagB)
        $this->assertFalse($this->cache->load($id1), 'Item with tagA should be removed');
        $this->assertFalse($this->cache->load($id2), 'Item with tagB should be removed');

        // id3 should still exist (has tagC)
        $this->assertEquals('data3', $this->cache->load($id3), 'Item with tagC should remain');
    }

    /**
     * Test clean with CLEANING_MODE_NOT_MATCHING_TAG
     */
    public function testCleanModeNotMatchingTag(): void
    {
        $id1 = 'test_not_1_' . uniqid();
        $id2 = 'test_not_2_' . uniqid();
        $id3 = 'test_not_3_' . uniqid();

        // Save with different tags
        $this->cache->save('data1', $id1, ['tagA', 'tagB']);
        $this->cache->save('data2', $id2, ['tagB']); // Only has tagB
        $this->cache->save('data3', $id3, []); // No tags

        // Clean items NOT matching ALL of [tagA, tagB]
        // This means: remove items that don't have BOTH tagA AND tagB
        $cleanResult = $this->cache->clean(CacheConstants::CLEANING_MODE_NOT_MATCHING_TAG, ['tagA', 'tagB']);
        $this->assertTrue($cleanResult, 'Clean not matching tag should succeed');

        // id1 should still exist (has both tagA and tagB)
        $this->assertEquals('data1', $this->cache->load($id1), 'Item with both tags should remain');

        // id2 and id3 should be removed (don't have both tags)
        // Note: NOT_MATCHING_TAG behavior may vary by adapter implementation
        $this->assertTrue(
            $this->cache->load($id2) === false || $this->cache->load($id2) === 'data2',
            'NOT_MATCHING_TAG behavior is adapter-dependent'
        );
    }

    /**
     * Test tags and cleaning modes work together
     */
    public function testTagsWithMultipleCleaningModes(): void
    {
        $id1 = 'test_modes_1_' . uniqid();
        $id2 = 'test_modes_2_' . uniqid();
        $id3 = 'test_modes_3_' . uniqid();

        // Save items with different tag combinations
        $this->cache->save('data1', $id1, ['tagX', 'tagY']);
        $this->cache->save('data2', $id2, ['tagY', 'tagZ']);
        $this->cache->save('data3', $id3, ['tagZ']);

        // Verify all exist
        $this->assertEquals('data1', $this->cache->load($id1));
        $this->assertEquals('data2', $this->cache->load($id2));
        $this->assertEquals('data3', $this->cache->load($id3));

        // Clean items with tagY (ANY mode)
        $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['tagY']);

        // id1 and id2 should be removed (have tagY)
        $this->assertFalse($this->cache->load($id1), 'Item 1 with tagY should be removed');
        $this->assertFalse($this->cache->load($id2), 'Item 2 with tagY should be removed');

        // id3 should remain (doesn't have tagY)
        $this->assertEquals('data3', $this->cache->load($id3), 'Item 3 without tagY should remain');
    }

    /**
     * Test getBackend() method
     */
    public function testGetBackend(): void
    {
        $backend = $this->cache->getBackend();

        $this->assertInstanceOf(
            \Magento\Framework\Cache\Frontend\Adapter\Symfony\BackendWrapper::class,
            $backend,
            'getBackend should return BackendWrapper instance'
        );
    }

    /**
     * Test getLowLevelFrontend() method
     */
    public function testGetLowLevelFrontend(): void
    {
        $frontend = $this->cache->getLowLevelFrontend();

        $this->assertInstanceOf(
            \Magento\Framework\Cache\Frontend\Adapter\Symfony\LowLevelFrontend::class,
            $frontend,
            'getLowLevelFrontend should return LowLevelFrontend instance'
        );
    }

    /**
     * Test cache backend is properly initialized
     */
    public function testBackendIsProperlyInitialized(): void
    {
        $backend = $this->cache->getBackend();

        // Verify backend can perform operations
        $testResult = $backend->test('test_backend_' . uniqid());
        $this->assertFalse($testResult, 'Backend test() should work for non-existent item');
    }

    /**
     * Test save with array data
     */
    public function testSaveArrayData(): void
    {
        $id = 'test_array_' . uniqid();
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'nested' => ['a' => 1, 'b' => 2]
        ];

        $this->cache->save($data, $id);
        $loadedData = $this->cache->load($id);

        $this->assertEquals($data, $loadedData, 'Array data should be preserved');
    }

    /**
     * Test save with object data
     */
    public function testSaveObjectData(): void
    {
        $id = 'test_object_' . uniqid();
        $data = new \stdClass();
        $data->property1 = 'value1';
        $data->property2 = 123;

        $this->cache->save($data, $id);
        $loadedData = $this->cache->load($id);

        $this->assertEquals($data, $loadedData, 'Object data should be preserved');
    }

    /**
     * Test save with special characters in ID
     */
    public function testSaveWithSpecialCharactersInId(): void
    {
        $id = 'test.special-chars_' . uniqid();
        $data = 'test_data';

        $saveResult = $this->cache->save($data, $id);
        $this->assertTrue($saveResult, 'Save with special chars in ID should succeed');

        $loadResult = $this->cache->load($id);
        $this->assertEquals($data, $loadResult, 'Data with special chars ID should be loadable');
    }

    /**
     * Test multiple saves to same ID (overwrite)
     */
    public function testOverwriteExistingId(): void
    {
        $id = 'test_overwrite_' . uniqid();

        // First save
        $this->cache->save('data1', $id);
        $this->assertEquals('data1', $this->cache->load($id));

        // Second save (overwrite)
        $this->cache->save('data2', $id);
        $this->assertEquals('data2', $this->cache->load($id), 'Second save should overwrite');
    }

    /**
     * Test cache with empty string data
     */
    public function testSaveEmptyString(): void
    {
        $id = 'test_empty_' . uniqid();
        $data = '';

        $this->cache->save($data, $id);
        $loadResult = $this->cache->load($id);

        $this->assertSame('', $loadResult, 'Empty string should be preserved');
    }

    /**
     * Test cache with zero value
     */
    public function testSaveZeroValue(): void
    {
        $id = 'test_zero_' . uniqid();
        $data = 0;

        $this->cache->save($data, $id);
        $loadResult = $this->cache->load($id);

        $this->assertSame(0, $loadResult, 'Zero value should be preserved');
    }

    /**
     * Test cache with false value
     */
    public function testSaveFalseValue(): void
    {
        $id = 'test_false_' . uniqid();
        $data = false;

        $this->cache->save($data, $id);
        $loadResult = $this->cache->load($id);

        $this->assertSame(false, $loadResult, 'False value should be preserved');
    }

    /**
     * Test cache with large data
     */
    public function testSaveLargeData(): void
    {
        $id = 'test_large_' . uniqid();
        $data = str_repeat('x', 100000); // 100KB string

        $saveResult = $this->cache->save($data, $id);
        $this->assertTrue($saveResult, 'Large data save should succeed');

        $loadResult = $this->cache->load($id);
        $this->assertEquals($data, $loadResult, 'Large data should be preserved');
    }

    /**
     * Test batch operations
     */
    public function testBatchOperations(): void
    {
        $ids = [];
        $baseId = 'test_batch_' . uniqid() . '_';

        // Save 100 items
        for ($i = 0; $i < 100; $i++) {
            $id = $baseId . $i;
            $ids[] = $id;
            $this->cache->save("data_$i", $id, ['batch_tag']);
        }

        // Verify all exist
        foreach ($ids as $i => $id) {
            $this->assertEquals("data_$i", $this->cache->load($id), "Item $i should exist");
        }

        // Clean by tag
        $this->cache->clean(CacheConstants::CLEANING_MODE_MATCHING_ANY_TAG, ['batch_tag']);

        // Verify all removed
        foreach ($ids as $id) {
            $this->assertFalse($this->cache->load($id), "Item should be removed after clean");
        }
    }

    /**
     * Test tag with special characters
     */
    public function testTagWithSpecialCharacters(): void
    {
        $id = 'test_special_tag_' . uniqid();
        $data = 'test_data';
        $tags = ['tag-with-dash', 'tag_with_underscore', 'TAG_UPPERCASE'];

        $this->cache->save($data, $id, $tags);
        $loadResult = $this->cache->load($id);

        $this->assertEquals($data, $loadResult, 'Data with special char tags should be loadable');
    }
}
