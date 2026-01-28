<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Frontend\Adapter\Symfony;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Frontend\Adapter\Symfony\MagentoDatabaseAdapter;
use Magento\Framework\DB\Adapter\AdapterInterface as DbAdapterInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\CacheItem;

/**
 * Unit test for MagentoDatabaseAdapter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MagentoDatabaseAdapterTest extends TestCase
{
    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var Serialize|MockObject
     */
    private $serializerMock;

    /**
     * @var DbAdapterInterface|MockObject
     */
    private $dbAdapterMock;

    /**
     * @var MagentoDatabaseAdapter
     */
    private MagentoDatabaseAdapter $adapter;

    /**
     * Set up test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->dbAdapterMock = $this->createMock(DbAdapterInterface::class);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->serializerMock = $this->createMock(Serialize::class);

        $this->resourceMock
            ->method('getConnection')
            ->willReturn($this->dbAdapterMock);

        $this->resourceMock
            ->method('getTableName')
            ->willReturnCallback(function ($tableName) {
                return $tableName; // Return table name as-is for testing
            });

        $this->adapter = new MagentoDatabaseAdapter(
            $this->resourceMock,
            $this->serializerMock,
            'test_',
            3600
        );
    }

    /**
     * Test constructor sets up Database backend correctly
     */
    public function testConstructorCreatesDatabaseBackend(): void
    {
        $backend = $this->adapter->getBackend();

        $this->assertInstanceOf(Database::class, $backend);
    }

    /**
     * Test constructor with empty namespace
     */
    public function testConstructorWithEmptyNamespace(): void
    {
        $adapter = new MagentoDatabaseAdapter(
            $this->resourceMock,
            $this->serializerMock,
            '',
            0
        );

        $this->assertInstanceOf(Database::class, $adapter->getBackend());
    }

    /**
     * Test getItem() returns cache hit for existing item
     */
    public function testGetItemReturnsHitForExistingItem(): void
    {
        $key = 'test_key';
        $value = 'test_value';
        $dataStructure = [
            'data' => $value,
            'tags' => ['tag1'],
            'tag_versions' => ['tag1' => 'version1'],
            'mtime' => time(),
            'expire' => time() + 3600
        ];

        // Use Magento serializer to prepare test data
        $realSerializer = new Serialize();
        $serializedData = $realSerializer->serialize($dataStructure);

        // Mock backend load
        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('load')
            ->with('test_' . $key)
            ->willReturn($serializedData);

        // Inject mocked backend via reflection
        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        // Mock serializer
        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedData)
            ->willReturn($dataStructure);

        $item = $this->adapter->getItem($key);

        $this->assertTrue($item->isHit());
        $this->assertEquals($key, $item->getKey());
        $this->assertEquals($value, $item->get());
    }

    /**
     * Test getItem() returns cache miss for non-existent item
     */
    public function testGetItemReturnsMissForNonExistentItem(): void
    {
        $key = 'missing_key';

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('load')
            ->with('test_' . $key)
            ->willReturn(false);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $item = $this->adapter->getItem($key);

        $this->assertFalse($item->isHit());
        $this->assertEquals($key, $item->getKey());
    }

    /**
     * Test getItem() handles old format (backward compatibility)
     */
    public function testGetItemHandlesOldFormat(): void
    {
        $key = 'old_key';
        $oldFormatData = [
            'value' => 'old_value',
            'tags' => ['tag1', 'tag2'],
            'expire' => time() + 3600
        ];

        // Use Magento serializer to prepare test data
        $realSerializer = new Serialize();
        $serializedData = $realSerializer->serialize($oldFormatData);

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($serializedData);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->willReturn($oldFormatData);

        $item = $this->adapter->getItem($key);

        $this->assertTrue($item->isHit());
    }

    /**
     * Test getItem() handles simple non-array values
     */
    public function testGetItemHandlesSimpleValues(): void
    {
        $key = 'simple_key';
        $simpleValue = 'simple_string';

        // Use Magento serializer to prepare test data
        $realSerializer = new Serialize();
        $serializedData = $realSerializer->serialize($simpleValue);

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('load')
            ->willReturn($serializedData);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->willReturn($simpleValue);

        $item = $this->adapter->getItem($key);

        $this->assertTrue($item->isHit());
        $this->assertEquals($simpleValue, $item->get());
    }

    /**
     * Test getItems() returns multiple items
     */
    public function testGetItemsReturnsMultipleItems(): void
    {
        $keys = ['key1', 'key2', 'key3'];

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['load'])
            ->getMock();

        $backendMock
            ->method('load')
            ->willReturn(false);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $items = $this->adapter->getItems($keys);

        $this->assertCount(3, $items);
        $this->assertArrayHasKey('key1', $items);
        $this->assertArrayHasKey('key2', $items);
        $this->assertArrayHasKey('key3', $items);
    }

    /**
     * Test getItems() with empty array
     */
    public function testGetItemsWithEmptyArray(): void
    {
        $items = $this->adapter->getItems([]);

        $this->assertEmpty($items);
    }

    /**
     * Test hasItem() returns true for existing item
     */
    public function testHasItemReturnsTrueForExistingItem(): void
    {
        $key = 'existing_key';

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['test'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('test')
            ->with('test_' . $key)
            ->willReturn(time());

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->hasItem($key);

        $this->assertTrue($result);
    }

    /**
     * Test hasItem() returns false for non-existent item
     */
    public function testHasItemReturnsFalseForNonExistentItem(): void
    {
        $key = 'missing_key';

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['test'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('test')
            ->with('test_' . $key)
            ->willReturn(false);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->hasItem($key);

        $this->assertFalse($result);
    }

    /**
     * Test clear() calls backend clean with CLEANING_MODE_ALL
     */
    public function testClearCallsBackendClean(): void
    {
        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clean'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('clean')
            ->with(CacheConstants::CLEANING_MODE_ALL)
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->clear();

        $this->assertTrue($result);
    }

    /**
     * Test clear() with prefix parameter (prefix is ignored)
     */
    public function testClearWithPrefix(): void
    {
        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['clean'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('clean')
            ->with(CacheConstants::CLEANING_MODE_ALL)
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        // Prefix parameter is accepted but not used
        $result = $this->adapter->clear('some_prefix');

        $this->assertTrue($result);
    }

    /**
     * Test deleteItem() removes single item
     */
    public function testDeleteItemRemovesSingleItem(): void
    {
        $key = 'item_to_delete';

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('remove')
            ->with('test_' . $key)
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->deleteItem($key);

        $this->assertTrue($result);
    }

    /**
     * Test deleteItem() returns false on failure
     */
    public function testDeleteItemReturnsFalseOnFailure(): void
    {
        $key = 'failed_delete';

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('remove')
            ->with('test_' . $key)
            ->willReturn(false);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->deleteItem($key);

        $this->assertFalse($result);
    }

    /**
     * Test deleteItems() removes multiple items
     */
    public function testDeleteItemsRemovesMultipleItems(): void
    {
        $keys = ['key1', 'key2', 'key3'];

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove'])
            ->getMock();

        $backendMock
            ->expects($this->exactly(3))
            ->method('remove')
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->deleteItems($keys);

        $this->assertTrue($result);
    }

    /**
     * Test deleteItems() returns false if any deletion fails
     */
    public function testDeleteItemsReturnsFalseIfAnyFails(): void
    {
        $keys = ['key1', 'key2', 'key3'];

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['remove'])
            ->getMock();

        $backendMock
            ->method('remove')
            ->willReturnOnConsecutiveCalls(true, false, true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $result = $this->adapter->deleteItems($keys);

        $this->assertFalse($result);
    }

    /**
     * Test save() saves item with tags
     */
    public function testSaveSavesItemWithTags(): void
    {
        $item = new CacheItem();
        $reflection = new \ReflectionClass($item);

        // Set key
        $keyProperty = $reflection->getProperty('key');
        $keyProperty->setValue($item, 'save_key');

        // Set value
        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setValue($item, 'save_value');

        // Set expiry
        $expiryProperty = $reflection->getProperty('expiry');
        $expiryProperty->setValue($item, time() + 3600);

        // Set newMetadata with tags (simulates TagAwareAdapter)
        $newMetadataProperty = $reflection->getProperty('newMetadata');
        $newMetadataProperty->setValue($item, [
            CacheItem::METADATA_TAGS => ['tag1' => 'version1', 'tag2' => 'version2']
        ]);

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->isType('string'),
                'test_save_key',
                ['tag1', 'tag2'],
                $this->greaterThan(0)
            )
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->willReturn('serialized_data');

        $result = $this->adapter->save($item);

        $this->assertTrue($result);
    }

    /**
     * Test save() without tags
     */
    public function testSaveWithoutTags(): void
    {
        $item = new CacheItem();
        $reflection = new \ReflectionClass($item);

        $keyProperty = $reflection->getProperty('key');
        $keyProperty->setValue($item, 'simple_key');

        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setValue($item, 'simple_value');

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->isType('string'),
                'test_simple_key',
                [], // No tags
                $this->greaterThanOrEqual(0)
            )
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->expects($this->once())
            ->method('serialize')
            ->willReturn('serialized_data');

        $result = $this->adapter->save($item);

        $this->assertTrue($result);
    }

    /**
     * Test saveDeferred() stores items for later commit
     */
    public function testSaveDeferredStoresItems(): void
    {
        $item1 = new CacheItem();
        $reflection1 = new \ReflectionClass($item1);
        $keyProperty1 = $reflection1->getProperty('key');
        $keyProperty1->setValue($item1, 'deferred1');

        $item2 = new CacheItem();
        $reflection2 = new \ReflectionClass($item2);
        $keyProperty2 = $reflection2->getProperty('key');
        $keyProperty2->setValue($item2, 'deferred2');

        $result1 = $this->adapter->saveDeferred($item1);
        $result2 = $this->adapter->saveDeferred($item2);

        $this->assertTrue($result1);
        $this->assertTrue($result2);
    }

    /**
     * Test commit() saves all deferred items
     */
    public function testCommitSavesAllDeferredItems(): void
    {
        $item1 = new CacheItem();
        $reflection1 = new \ReflectionClass($item1);
        $keyProperty1 = $reflection1->getProperty('key');
        $keyProperty1->setValue($item1, 'commit1');
        $valueProperty1 = $reflection1->getProperty('value');
        $valueProperty1->setValue($item1, 'value1');

        $item2 = new CacheItem();
        $reflection2 = new \ReflectionClass($item2);
        $keyProperty2 = $reflection2->getProperty('key');
        $keyProperty2->setValue($item2, 'commit2');
        $valueProperty2 = $reflection2->getProperty('value');
        $valueProperty2->setValue($item2, 'value2');

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $backendMock
            ->expects($this->exactly(2))
            ->method('save')
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->method('serialize')
            ->willReturn('serialized');

        $this->adapter->saveDeferred($item1);
        $this->adapter->saveDeferred($item2);
        $result = $this->adapter->commit();

        $this->assertTrue($result);
    }

    /**
     * Test commit() clears deferred queue after saving
     */
    public function testCommitClearsDeferredQueue(): void
    {
        $item = new CacheItem();
        $reflection = new \ReflectionClass($item);
        $keyProperty = $reflection->getProperty('key');
        $keyProperty->setValue($item, 'clear_test');
        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setValue($item, 'value');

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $backendMock
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->method('serialize')
            ->willReturn('serialized');

        $this->adapter->saveDeferred($item);
        $this->adapter->commit();

        // Second commit should not save anything (queue is empty)
        $backendMock
            ->expects($this->never())
            ->method('save');

        $result = $this->adapter->commit();
        $this->assertTrue($result);
    }

    /**
     * Test commit() returns false if any save fails
     */
    public function testCommitReturnsFalseIfAnySaveFails(): void
    {
        $item1 = new CacheItem();
        $reflection1 = new \ReflectionClass($item1);
        $keyProperty1 = $reflection1->getProperty('key');
        $keyProperty1->setValue($item1, 'fail1');
        $valueProperty1 = $reflection1->getProperty('value');
        $valueProperty1->setValue($item1, 'value1');

        $item2 = new CacheItem();
        $reflection2 = new \ReflectionClass($item2);
        $keyProperty2 = $reflection2->getProperty('key');
        $keyProperty2->setValue($item2, 'fail2');
        $valueProperty2 = $reflection2->getProperty('value');
        $valueProperty2->setValue($item2, 'value2');

        $backendMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save'])
            ->getMock();

        $backendMock
            ->method('save')
            ->willReturnOnConsecutiveCalls(true, false);

        $reflection = new \ReflectionClass($this->adapter);
        $backendProperty = $reflection->getProperty('backend');
        $backendProperty->setValue($this->adapter, $backendMock);

        $this->serializerMock
            ->method('serialize')
            ->willReturn('serialized');

        $this->adapter->saveDeferred($item1);
        $this->adapter->saveDeferred($item2);
        $result = $this->adapter->commit();

        $this->assertFalse($result);
    }

    /**
     * Test getBackend() returns Database instance
     */
    public function testGetBackendReturnsDatabaseInstance(): void
    {
        $backend = $this->adapter->getBackend();

        $this->assertInstanceOf(Database::class, $backend);
    }
}
