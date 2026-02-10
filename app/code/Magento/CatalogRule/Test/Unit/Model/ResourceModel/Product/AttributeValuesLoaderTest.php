<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Test\Unit\Model\ResourceModel\Product;

use Magento\CatalogRule\Model\Indexer\DynamicBatchSizeCalculator;
use Magento\CatalogRule\Model\ResourceModel\Product\AttributeValuesLoader;
use Magento\CatalogRule\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for AttributeValuesLoader
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeValuesLoaderTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Collection|MockObject
     */
    private $collectionMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attributeMock;

    /**
     * @var DynamicBatchSizeCalculator|MockObject
     */
    private $batchSizeCalculatorMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @var Select|MockObject
     */
    private $selectMock;

    /**
     * @var AttributeValuesLoader
     */
    private $loader;

    /**
     * @var int
     */
    private $batchSize = 100;

    /**
     * @var int
     */
    private $maxBatchesInMemory = 2;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->collectionMock = $this->createMock(Collection::class);
        $this->attributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getId', 'getBackend', 'getEntity']
        );
        $this->batchSizeCalculatorMock = $this->createMock(DynamicBatchSizeCalculator::class);
        $this->connectionMock = $this->createMock(AdapterInterface::class);
        $this->selectMock = $this->createMock(Select::class);

        $this->batchSizeCalculatorMock->method('getAttributeBatchSize')
            ->willReturn($this->batchSize);
        $this->batchSizeCalculatorMock->method('getMaxBatchesInMemory')
            ->willReturn($this->maxBatchesInMemory);

        $this->collectionMock->method('getConnection')
            ->willReturn($this->connectionMock);
    }

    /**
     * Test count returns collection size
     *
     * @return void
     */
    public function testCountReturnsCollectionSize(): void
    {
        $expectedCount = 500;

        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($expectedCount);

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->assertEquals($expectedCount, $this->loader->count());
    }

    /**
     * Test count caches the result
     *
     * @return void
     */
    public function testCountCachesResult(): void
    {
        $expectedCount = 500;

        $this->collectionMock->expects($this->once())
            ->method('getSize')
            ->willReturn($expectedCount);

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->assertEquals($expectedCount, $this->loader->count());
        $this->assertEquals($expectedCount, $this->loader->count()); // Should use cached value
    }

    /**
     * Test offsetExists returns true for loaded entity
     *
     * @return void
     */
    public function testOffsetExistsReturnsTrueForLoadedEntity(): void
    {
        $entityId = 50;
        $this->setupBatchLoadMocks($entityId);

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->assertTrue($this->loader->offsetExists($entityId));
    }

    /**
     * Test offsetExists returns false for non-existent entity
     *
     * @return void
     */
    public function testOffsetExistsReturnsFalseForNonExistentEntity(): void
    {
        $entityId = 999;
        $this->setupEmptyBatchLoadMocks();

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->assertFalse($this->loader->offsetExists($entityId));
    }

    /**
     * Test offsetGet returns attribute values
     *
     * @return void
     */
    public function testOffsetGetReturnsAttributeValues(): void
    {
        $entityId = 50;
        $expectedValues = [0 => 'value1', 1 => 'value2'];

        $this->setupBatchLoadMocks($entityId, $expectedValues);

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->assertEquals($expectedValues, $this->loader->offsetGet($entityId));
    }

    /**
     * Test offsetGet returns null for non-existent entity
     *
     * @return void
     */
    public function testOffsetGetReturnsNullForNonExistentEntity(): void
    {
        $entityId = 999;
        $this->setupEmptyBatchLoadMocks();

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->assertNull($this->loader->offsetGet($entityId));
    }

    /**
     * Test offsetSet throws exception
     *
     * @return void
     */
    public function testOffsetSetThrowsException(): void
    {
        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('AttributeValuesLoader is read-only');

        $this->loader->offsetSet(1, ['value']);
    }

    /**
     * Test offsetUnset throws exception
     *
     * @return void
     */
    public function testOffsetUnsetThrowsException(): void
    {
        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('AttributeValuesLoader is read-only');

        $this->loader->offsetUnset(1);
    }

    /**
     * Test batch loading with correct batch boundaries
     *
     * @return void
     */
    public function testBatchLoadingWithCorrectBoundaries(): void
    {
        $entityId = 150; // Should load batch starting at 100 (floor(150/100) * 100)
        $batchStartId = 100;

        $this->setupBatchLoadMocksWithBoundaryCheck($batchStartId);

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $this->loader->offsetGet($entityId);
    }

    /**
     * Test that accessing same batch twice doesn't reload
     *
     * @return void
     */
    public function testAccessingSameBatchTwiceDoesntReload(): void
    {
        $entityId1 = 50;
        $entityId2 = 75;

        $this->setupBatchLoadMocks($entityId1, [0 => 'value1']);

        $this->loader = new AttributeValuesLoader(
            $this->collectionMock,
            $this->attributeMock,
            $this->batchSizeCalculatorMock
        );

        $result1 = $this->loader->offsetGet($entityId1);
        $result2 = $this->loader->offsetGet($entityId2);

        $this->assertIsArray($result1);
        $this->assertNull($result2);
    }

    /**
     * Setup mocks for batch loading
     *
     * @param int $entityId
     * @param array $values
     * @return void
     */
    private function setupBatchLoadMocks(int $entityId, array $values = [0 => 'testvalue']): void
    {
        $attributeId = 123;
        $backendMock = $this->createMock(AbstractBackend::class);
        $entityMock = $this->createMock(AbstractEntity::class);

        $this->attributeMock->method('getId')->willReturn($attributeId);
        $this->attributeMock->method('getBackend')->willReturn($backendMock);
        $this->attributeMock->method('getEntity')->willReturn($entityMock);

        $backendMock->method('getTable')->willReturn('catalog_product_entity_varchar');
        $entityMock->method('getLinkField')->willReturn('entity_id');

        $this->collectionMock->method('getMainTable')
            ->willReturn('catalog_product_entity');

        $this->connectionMock->method('getAutoIncrementField')
            ->willReturn('entity_id');

        $this->connectionMock->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->method('from')->willReturnSelf();
        $this->selectMock->method('join')->willReturnSelf();
        $this->selectMock->method('where')->willReturnSelf();
        $this->selectMock->method('order')->willReturnSelf();

        $stmtMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $rows = [];
        foreach ($values as $storeId => $value) {
            $rows[] = ['entity_id' => $entityId, 'store_id' => $storeId, 'value' => $value];
        }

        $stmtMock->method('fetch')
            ->willReturnOnConsecutiveCalls(...array_merge($rows, [false]));

        $this->connectionMock->method('query')
            ->willReturn($stmtMock);
    }

    /**
     * Setup mocks for empty batch loading
     *
     * @return void
     */
    private function setupEmptyBatchLoadMocks(): void
    {
        $attributeId = 123;
        $backendMock = $this->createMock(AbstractBackend::class);
        $entityMock = $this->createMock(AbstractEntity::class);

        $this->attributeMock->method('getId')->willReturn($attributeId);
        $this->attributeMock->method('getBackend')->willReturn($backendMock);
        $this->attributeMock->method('getEntity')->willReturn($entityMock);

        $backendMock->method('getTable')->willReturn('catalog_product_entity_varchar');
        $entityMock->method('getLinkField')->willReturn('entity_id');

        $this->collectionMock->method('getMainTable')
            ->willReturn('catalog_product_entity');

        $this->connectionMock->method('getAutoIncrementField')
            ->willReturn('entity_id');

        $this->connectionMock->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->method('from')->willReturnSelf();
        $this->selectMock->method('join')->willReturnSelf();
        $this->selectMock->method('where')->willReturnSelf();
        $this->selectMock->method('order')->willReturnSelf();

        $stmtMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $stmtMock->method('fetch')->willReturn(false);

        $this->connectionMock->method('query')
            ->willReturn($stmtMock);
    }

    /**
     * Setup mocks with boundary check
     *
     * @param int $expectedBatchStart
     * @return void
     */
    private function setupBatchLoadMocksWithBoundaryCheck(int $expectedBatchStart): void
    {
        $attributeId = 123;
        $backendMock = $this->createMock(AbstractBackend::class);
        $entityMock = $this->createMock(AbstractEntity::class);

        $this->attributeMock->method('getId')->willReturn($attributeId);
        $this->attributeMock->method('getBackend')->willReturn($backendMock);
        $this->attributeMock->method('getEntity')->willReturn($entityMock);

        $backendMock->method('getTable')->willReturn('catalog_product_entity_varchar');
        $entityMock->method('getLinkField')->willReturn('entity_id');

        $this->collectionMock->method('getMainTable')
            ->willReturn('catalog_product_entity');

        $this->connectionMock->method('getAutoIncrementField')
            ->willReturn('entity_id');

        $this->connectionMock->method('select')
            ->willReturn($this->selectMock);

        $this->selectMock->method('from')->willReturnSelf();
        $this->selectMock->method('join')->willReturnSelf();

        $whereCallCount = 0;
        $this->selectMock->expects($this->exactly(3))
            ->method('where')
            ->willReturnCallback(
                function ($arg1, $arg2 = null) use ($attributeId, $expectedBatchStart, &$whereCallCount) {
                    $whereCallCount++;
                    if ($whereCallCount === 1) {
                        $this->assertEquals('attribute_id = ?', $arg1);
                        $this->assertEquals($attributeId, $arg2);
                    } elseif ($whereCallCount === 2) {
                        $this->assertEquals('cpe.entity_id >= ?', $arg1);
                        $this->assertEquals($expectedBatchStart, $arg2);
                    } elseif ($whereCallCount === 3) {
                        $this->assertEquals('cpe.entity_id < ?', $arg1);
                        $this->assertEquals($expectedBatchStart + $this->batchSize, $arg2);
                    }
                    return $this->selectMock;
                }
            );
        $this->selectMock->method('order')->willReturnSelf();

        $stmtMock = $this->createMock(\Zend_Db_Statement_Interface::class);
        $stmtMock->method('fetch')->willReturn(false);

        $this->connectionMock->method('query')
            ->willReturn($stmtMock);
    }
}
