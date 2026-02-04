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
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DB\Adapter\AdapterInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Collection::getAllAttributeValues
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var DynamicBatchSizeCalculator|MockObject
     */
    private $batchSizeCalculatorMock;

    /**
     * @var AbstractEntity|MockObject
     */
    private $entityMock;

    /**
     * @var AbstractAttribute|MockObject
     */
    private $attributeMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connectionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->batchSizeCalculatorMock = $this->createMock(DynamicBatchSizeCalculator::class);
        $this->entityMock = $this->createMock(AbstractEntity::class);
        $this->attributeMock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getId', 'getBackend', 'getEntity']
        );
        $this->connectionMock = $this->createMock(AdapterInterface::class);

        // Create actual collection instance with mocked dependencies
        $this->collection = $this->createPartialMock(
            Collection::class,
            ['getEntity', 'getConnection', 'getMainTable']
        );

        $this->collection->method('getEntity')
            ->willReturn($this->entityMock);
        $this->collection->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->collection->method('getMainTable')
            ->willReturn('catalog_product_entity');

        // Inject batch size calculator using constructor
        $this->injectBatchSizeCalculator();
    }

    /**
     * Inject batch size calculator into collection
     *
     * @return void
     */
    private function injectBatchSizeCalculator(): void
    {
        $reflection = new \ReflectionClass(Collection::class);
        $property = $reflection->getProperty('batchSizeCalculator');
        $property->setValue($this->collection, $this->batchSizeCalculatorMock);
    }

    /**
     * Test getAllAttributeValues with string attribute code
     *
     * @return void
     */
    public function testGetAllAttributeValuesWithStringAttributeCode(): void
    {
        $attributeCode = 'test_attribute';
        $attributeId = 123;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->entityMock->expects($this->once())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getAttributeBatchSize')
            ->willReturn(1000);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getMaxBatchesInMemory')
            ->willReturn(2);

        $result = $this->collection->getAllAttributeValues($attributeCode);

        $this->assertInstanceOf(AttributeValuesLoader::class, $result);
    }

    /**
     * Test getAllAttributeValues with AbstractAttribute object
     *
     * @return void
     */
    public function testGetAllAttributeValuesWithAttributeObject(): void
    {
        $attributeId = 456;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getAttributeBatchSize')
            ->willReturn(1000);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getMaxBatchesInMemory')
            ->willReturn(2);

        $result = $this->collection->getAllAttributeValues($this->attributeMock);

        $this->assertInstanceOf(AttributeValuesLoader::class, $result);
    }

    /**
     * Test getAllAttributeValues returns cached instance for same attribute
     *
     * @return void
     */
    public function testGetAllAttributeValuesReturnsCachedInstance(): void
    {
        $attributeCode = 'test_attribute';
        $attributeId = 123;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->entityMock->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->batchSizeCalculatorMock->method('getAttributeBatchSize')
            ->willReturn(1000);

        $this->batchSizeCalculatorMock->method('getMaxBatchesInMemory')
            ->willReturn(2);

        $result1 = $this->collection->getAllAttributeValues($attributeCode);
        $result2 = $this->collection->getAllAttributeValues($attributeCode);

        $this->assertSame($result1, $result2, 'Should return the same cached instance');
    }

    /**
     * Test getAllAttributeValues returns different instances for different attributes
     *
     * @return void
     */
    public function testGetAllAttributeValuesReturnsDifferentInstancesForDifferentAttributes(): void
    {
        $attributeCode1 = 'test_attribute_1';
        $attributeId1 = 123;
        $attribute1Mock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getId', 'getBackend', 'getEntity']
        );
        $attribute1Mock->method('getId')->willReturn($attributeId1);

        $attributeCode2 = 'test_attribute_2';
        $attributeId2 = 456;
        $attribute2Mock = $this->createPartialMock(
            AbstractAttribute::class,
            ['getId', 'getBackend', 'getEntity']
        );
        $attribute2Mock->method('getId')->willReturn($attributeId2);

        $this->entityMock->method('getAttribute')
            ->willReturnMap([
                [$attributeCode1, $attribute1Mock],
                [$attributeCode2, $attribute2Mock]
            ]);

        $this->batchSizeCalculatorMock->method('getAttributeBatchSize')
            ->willReturn(1000);

        $this->batchSizeCalculatorMock->method('getMaxBatchesInMemory')
            ->willReturn(2);

        $result1 = $this->collection->getAllAttributeValues($attributeCode1);
        $result2 = $this->collection->getAllAttributeValues($attributeCode2);

        $this->assertNotSame($result1, $result2, 'Should return different instances for different attributes');
    }

    /**
     * Test getAllAttributeValues creates loader with correct dependencies
     *
     * @return void
     */
    public function testGetAllAttributeValuesCreatesLoaderWithCorrectDependencies(): void
    {
        $attributeCode = 'test_attribute';
        $attributeId = 789;
        $expectedBatchSize = 5000;
        $expectedMaxBatches = 10;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->entityMock->expects($this->once())
            ->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getAttributeBatchSize')
            ->willReturn($expectedBatchSize);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getMaxBatchesInMemory')
            ->willReturn($expectedMaxBatches);

        $result = $this->collection->getAllAttributeValues($attributeCode);

        $this->assertInstanceOf(AttributeValuesLoader::class, $result);
    }

    /**
     * Test getAllAttributeValues cache works across string and object calls
     *
     * @return void
     */
    public function testGetAllAttributeValuesCacheWorksAcrossStringAndObjectCalls(): void
    {
        $attributeCode = 'test_attribute';
        $attributeId = 999;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->entityMock->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->batchSizeCalculatorMock->method('getAttributeBatchSize')
            ->willReturn(1000);

        $this->batchSizeCalculatorMock->method('getMaxBatchesInMemory')
            ->willReturn(2);

        // First call with string
        $result1 = $this->collection->getAllAttributeValues($attributeCode);

        // Second call with same attribute object (same ID)
        $result2 = $this->collection->getAllAttributeValues($this->attributeMock);

        $this->assertSame($result1, $result2, 'Should return same cached instance regardless of input type');
    }

    /**
     * Test getAllAttributeValues works with various batch sizes
     *
     * @param int $batchSize
     * @param int $maxBatches
     * @return void
     */
    #[DataProvider('batchSizeProvider')]
    public function testGetAllAttributeValuesWorksWithVariousBatchSizes(int $batchSize, int $maxBatches): void
    {
        $attributeCode = 'test_attribute';
        $attributeId = 100;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->entityMock->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getAttributeBatchSize')
            ->willReturn($batchSize);

        $this->batchSizeCalculatorMock->expects($this->once())
            ->method('getMaxBatchesInMemory')
            ->willReturn($maxBatches);

        $result = $this->collection->getAllAttributeValues($attributeCode);

        $this->assertInstanceOf(AttributeValuesLoader::class, $result);
    }

    /**
     * Data provider for batch size tests
     *
     * @return array
     */
    public static function batchSizeProvider(): array
    {
        return [
            'small_batch' => [500, 2],
            'medium_batch' => [1000, 5],
            'large_batch' => [5000, 10],
            'very_large_batch' => [10000, 20],
        ];
    }

    /**
     * Test that cache is properly isolated per collection instance
     *
     * @return void
     */
    public function testCacheIsIsolatedPerCollectionInstance(): void
    {
        $attributeCode = 'test_attribute';
        $attributeId = 111;

        $this->attributeMock->method('getId')
            ->willReturn($attributeId);

        $this->entityMock->method('getAttribute')
            ->with($attributeCode)
            ->willReturn($this->attributeMock);

        $this->batchSizeCalculatorMock->method('getAttributeBatchSize')
            ->willReturn(1000);

        $this->batchSizeCalculatorMock->method('getMaxBatchesInMemory')
            ->willReturn(2);

        $result1 = $this->collection->getAllAttributeValues($attributeCode);

        $collection2 = $this->createPartialMock(
            Collection::class,
            ['getEntity', 'getConnection', 'getMainTable']
        );

        $collection2->method('getEntity')
            ->willReturn($this->entityMock);
        $collection2->method('getConnection')
            ->willReturn($this->connectionMock);
        $collection2->method('getMainTable')
            ->willReturn('catalog_product_entity');

        $reflection = new \ReflectionClass(Collection::class);
        $property = $reflection->getProperty('batchSizeCalculator');
        $property->setValue($collection2, $this->batchSizeCalculatorMock);

        $result2 = $collection2->getAllAttributeValues($attributeCode);

        $this->assertNotSame($result1, $result2, 'Cache should be isolated per collection instance');
    }
}
