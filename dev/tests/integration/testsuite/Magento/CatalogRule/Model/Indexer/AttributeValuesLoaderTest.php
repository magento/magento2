<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\Indexer;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\CatalogRule\Model\Indexer\DynamicBatchSizeCalculator;
use Magento\CatalogRule\Model\ResourceModel\Product\AttributeValuesLoader;
use Magento\CatalogRule\Model\ResourceModel\Product\Collection;
use Magento\Eav\Model\Config as EavConfig;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Fixture\DbIsolation;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for AttributeValuesLoader
 */
#[
    AppArea('crontab'),
    DbIsolation(true)
]
class AttributeValuesLoaderTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var DynamicBatchSizeCalculator
     */
    private $batchSizeCalculator;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->collection = $objectManager->create(Collection::class);
        $this->eavConfig = $objectManager->get(EavConfig::class);
        $this->batchSizeCalculator = $objectManager->get(DynamicBatchSizeCalculator::class);
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    #[
        DataFixture(ProductFixture::class, ['name' => 'Product Alpha', 'sku' => 'loader-test-1'], 'product1'),
        DataFixture(ProductFixture::class, ['name' => 'Product Beta', 'sku' => 'loader-test-2'], 'product2'),
        DataFixture(ProductFixture::class, ['name' => 'Product Gamma', 'sku' => 'loader-test-3'], 'product3')
    ]
    public function testAttributeValuesLoaderWithRealData(): void
    {
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');
        $product3 = $this->fixtures->get('product3');

        $nameAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'name');

        $loader = new AttributeValuesLoader(
            $this->collection,
            $nameAttribute,
            $this->batchSizeCalculator
        );

        $this->assertGreaterThan(0, $loader->count());

        $values1 = $loader->offsetGet($product1->getId());
        $values2 = $loader->offsetGet($product2->getId());
        $values3 = $loader->offsetGet($product3->getId());

        $this->assertIsArray($values1);
        $this->assertIsArray($values2);
        $this->assertIsArray($values3);

        $this->assertNotEmpty($values1);
        $this->assertNotEmpty($values2);
        $this->assertNotEmpty($values3);
    }

    #[
        DataFixture(ProductFixture::class, ['price' => 99.99], 'product1'),
        DataFixture(ProductFixture::class, ['price' => 149.99], 'product2')
    ]
    public function testCollectionGetAllAttributeValues(): void
    {
        $product1 = $this->fixtures->get('product1');
        $product2 = $this->fixtures->get('product2');

        $loader = $this->collection->getAllAttributeValues('name');

        $this->assertInstanceOf(AttributeValuesLoader::class, $loader);

        $this->assertTrue($loader->offsetExists($product1->getId()));
        $this->assertTrue($loader->offsetExists($product2->getId()));

        $nameValues1 = $loader->offsetGet($product1->getId());
        $nameValues2 = $loader->offsetGet($product2->getId());

        $this->assertIsArray($nameValues1);
        $this->assertIsArray($nameValues2);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'cache-test-1'], 'product')
    ]
    public function testLoaderCaching(): void
    {
        $loader1 = $this->collection->getAllAttributeValues('sku');
        $loader2 = $this->collection->getAllAttributeValues('sku');

        $this->assertSame($loader1, $loader2, 'Collection should return cached loader instance');

        $loader3 = $this->collection->getAllAttributeValues('name');
        $this->assertNotSame($loader1, $loader3, 'Different attribute should have different loader');
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'batch-1'], 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'batch-2'], 'p2'),
        DataFixture(ProductFixture::class, ['sku' => 'batch-3'], 'p3'),
        DataFixture(ProductFixture::class, ['sku' => 'batch-4'], 'p4'),
        DataFixture(ProductFixture::class, ['sku' => 'batch-5'], 'p5')
    ]
    public function testBatchLoadingMemoryEfficiency(): void
    {
        $nameAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'name');

        $memoryBefore = memory_get_usage(true);

        $loader = new AttributeValuesLoader(
            $this->collection,
            $nameAttribute,
            $this->batchSizeCalculator
        );

        $memoryAfter = memory_get_usage(true);
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertLessThan(
            1024 * 1024, // 1MB
            $memoryUsed,
            sprintf('Loader initialization used %.2f MB, expected < 1MB', $memoryUsed / (1024 * 1024))
        );

        $product1 = $this->fixtures->get('p1');
        $values = $loader->offsetGet($product1->getId());

        $this->assertIsArray($values);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'count-1'], 'p1'),
        DataFixture(ProductFixture::class, ['sku' => 'count-2'], 'p2'),
        DataFixture(ProductFixture::class, ['sku' => 'count-3'], 'p3')
    ]
    public function testCountDoesNotTriggerFullLoad(): void
    {
        $nameAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'name');

        $loader = new AttributeValuesLoader(
            $this->collection,
            $nameAttribute,
            $this->batchSizeCalculator
        );

        $startTime = microtime(true);
        $count = $loader->count();
        $duration = microtime(true) - $startTime;

        $this->assertLessThan(
            0.1, // 100ms
            $duration,
            sprintf('count() took %.4fs, should be < 100ms', $duration)
        );

        $this->assertGreaterThan(0, $count);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'readonly-test'], 'product')
    ]
    public function testReadOnlyBehavior(): void
    {
        $nameAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'name');

        $loader = new AttributeValuesLoader(
            $this->collection,
            $nameAttribute,
            $this->batchSizeCalculator
        );

        $product = $this->fixtures->get('product');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('AttributeValuesLoader is read-only');

        $loader->offsetSet($product->getId(), ['test' => 'value']);
    }

    #[
        DataFixture(ProductFixture::class, ['sku' => 'unset-test'], 'product')
    ]
    public function testOffsetUnsetThrowsException(): void
    {
        $nameAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'name');

        $loader = new AttributeValuesLoader(
            $this->collection,
            $nameAttribute,
            $this->batchSizeCalculator
        );

        $product = $this->fixtures->get('product');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('AttributeValuesLoader is read-only');

        $loader->offsetUnset($product->getId());
    }

    public function testBatchSizeCalculation(): void
    {
        $batchSize = $this->batchSizeCalculator->getAttributeBatchSize();
        $maxBatches = $this->batchSizeCalculator->getMaxBatchesInMemory();

        $this->assertGreaterThanOrEqual(500, $batchSize, 'Batch size should be at least 500');
        $this->assertLessThanOrEqual(50000, $batchSize, 'Batch size should not exceed 50000');

        $this->assertGreaterThanOrEqual(2, $maxBatches, 'Should allow at least 2 batches in memory');
        $this->assertLessThanOrEqual(100, $maxBatches, 'Should not exceed 100 batches in memory');
    }
}
