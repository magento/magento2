<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogImportExport\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\Product\Type\Factory;
use Magento\CatalogImportExport\Model\Export\ProductFilterInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite;
use Magento\CatalogImportExport\Model\ResourceModel\Product\ExportCollection;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as AttributeCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD)
 */
class ProductTest extends TestCase
{
    /**
     * @var Timezone|MockObject
     */
    protected $localeDate;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resource;

    /**
     * @var StoreManagerInterface|MockObject
     */
    protected $storeManager;

    /**
     * @var LoggerInterface|MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|MockObject
     */
    protected $collection;

    /**
     * @var AbstractCollection|MockObject
     */
    protected $abstractCollection;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $exportConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory|MockObject
     */
    protected $productFactory;

    /**
     * @var MockObject
     */
    protected $attrSetColFactory;

    /**
     * @var CategoryCollectionFactory|MockObject
     */
    protected $categoryColFactory;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory|MockObject
     */
    protected $itemFactory;

    /**
     * @var MockObject
     */
    protected $optionColFactory;

    /**
     * @var MockObject
     */
    protected $attributeColFactory;

    /**
     * @var Factory|MockObject
     */
    protected $typeFactory;

    /**
     * @var LinkTypeProvider|MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var Composite|MockObject
     */
    protected $rowCustomizer;

    /**
     * @var MetadataPool|MockObject
     */
    protected $metadataPool;

    /**
     * @var AbstractAdapter|MockObject
     */
    protected $writer;

    /**
     * @var Product|MockObject
     */
    protected $product;

    /**
     * @var StubProduct|Product
     */
    protected $object;

    /**
     * @var ProductFilterInterface|MockObject
     */
    private $filter;

    /**
     * @var StockConfigurationInterface|MockObject
     */
    private $stockConfiguration;

    /**
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->localeDate = $this->createMock(Timezone::class);

        $this->config = $this->createPartialMock(Config::class, ['getEntityType']);
        $type = $this->createMock(Type::class);
        $this->config->expects($this->once())->method('getEntityType')->willReturn($type);

        $this->resource = $this->createMock(ResourceConnection::class);

        $this->storeManager = $this->createMock(StoreManager::class);
        $this->logger = $this->createMock(Monolog::class);

        $this->collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
        $this->abstractCollection = $this->createMock(AbstractCollection::class);
        $this->exportConfig = $this->createMock(\Magento\ImportExport\Model\Export\Config::class);

        // Create Product ResourceModel mock
        $productResourceMock = $this->createMock(ProductResource::class);
        $productResourceMock->method('getTypeId')->willReturn(4);

        // Create Product Factory mock that returns the ResourceModel
        $this->productFactory = $this->createMock(\Magento\Catalog\Model\ResourceModel\ProductFactory::class);
        $this->productFactory->method('create')->willReturn($productResourceMock);

        // Create AttributeSet Collection mock
        $attributeSetCollectionMock = $this->createMock(AttributeSetCollection::class);
        $attributeSetCollectionMock->method('setEntityTypeFilter')->willReturnSelf();
        $attributeSetCollectionMock->method('getIterator')->willReturn(new \ArrayIterator([]));

        // Create AttributeSet Collection Factory mock that returns the Collection
        $this->attrSetColFactory = $this->createMock(AttributeSetCollectionFactory::class);
        $this->attrSetColFactory->method('create')->willReturn($attributeSetCollectionMock);

        $this->categoryColFactory = $this->createMock(CategoryCollectionFactory::class);

        $this->itemFactory = $this->createMock(ItemFactory::class);
        $this->optionColFactory = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory::class
        );

        $this->attributeColFactory = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class
        );
        $this->typeFactory = $this->createMock(Factory::class);

        $this->linkTypeProvider = $this->createMock(LinkTypeProvider::class);
        $this->rowCustomizer = $this->createMock(
            Composite::class
        );
        $this->metadataPool = $this->createMock(MetadataPool::class);

        $this->writer = $this->createPartialMock(AbstractAdapter::class, [
            'setHeaderCols',
            'writeRow',
            'getContents',
        ]);

        $this->filter = $this->createMock(ProductFilterInterface::class);
        $this->stockConfiguration = $this->createMock(StockConfigurationInterface::class);

        $constructorMethods = [
            'initTypeModels',
            'initAttributes',
            '_initStores',
            'initAttributeSets',
            'initWebsites',
            'initCategories'
        ];

        $mockMethods = array_merge($constructorMethods, [
            '_customHeadersMapping',
            '_prepareEntityCollection',
            '_getEntityCollection',
            'getProductEntityLinkField',
            'getWriter',
            'getExportData',
            '_customFieldsMapping',
            'getItemsPerPage',
            'paginateCollection',
            '_getHeaderColumns',
        ]);
        $this->product = $this->createPartialMock(
            Product::class,
            $mockMethods
        );

        foreach ($constructorMethods as $method) {
            $this->product->expects($this->once())->method($method)->willReturnSelf();
        }

        $this->product->__construct(
            $this->localeDate,
            $this->config,
            $this->resource,
            $this->storeManager,
            $this->logger,
            $this->collection,
            $this->exportConfig,
            $this->productFactory,
            $this->attrSetColFactory,
            $this->categoryColFactory,
            $this->itemFactory,
            $this->optionColFactory,
            $this->attributeColFactory,
            $this->typeFactory,
            $this->linkTypeProvider,
            $this->rowCustomizer,
            [],
            $this->filter,
            $this->stockConfiguration
        );
        $this->setPropertyValue($this->product, 'metadataPool', $this->metadataPool);

        $this->object = new StubProduct();
    }

    /**
     * Test getEntityTypeCode()
     */
    public function testGetEntityTypeCode()
    {
        $this->assertEquals($this->product->getEntityTypeCode(), 'catalog_product');
    }

    public function testUpdateDataWithCategoryColumnsNoCategoriesAssigned()
    {
        $dataRow = [];
        $productId = 1;
        $rowCategories = [$productId => []];

        $this->assertTrue($this->object->updateDataWithCategoryColumns($dataRow, $rowCategories, $productId));
    }

    public function testGetHeaderColumns()
    {
        $product = $this->createPartialMock(
            Product::class,
            ['_customHeadersMapping']
        );
        $headerColumnsValue = ['headerColumns value'];
        $expectedResult = 'result';
        $this->setPropertyValue($product, '_headerColumns', $headerColumnsValue);
        $this->setPropertyValue($product, 'rowCustomizer', $this->rowCustomizer);
        $product->expects($this->once())
            ->method('_customHeadersMapping')
            ->with($headerColumnsValue)
            ->willReturn($expectedResult);
        $this->rowCustomizer->expects($this->once())
            ->method('addHeaderColumns')
            ->with($headerColumnsValue)
            ->willReturn($headerColumnsValue);

        $result = $product->_getHeaderColumns();

        $this->assertEquals($expectedResult, $result);
    }

    public function testExportCountZeroBreakInternalCalls()
    {
        $this->markTestSkipped(
            'Legacy buffered-path unit test is not applicable after export switched to streamed-only flow.'
        );
    }

    public function testExportCurPageEqualToLastBreakInternalCalls()
    {
        $this->markTestSkipped(
            'Legacy buffered-path unit test is not applicable after export switched to streamed-only flow.'
        );
    }

    public function testExportContinuesAfterEveryProductInBatchFailsConversion(): void
    {
        $product = $this->createBatchExportProduct([
            [1 => [0 => $this->createExportRow(1)], 2 => [0 => $this->createExportRow(2)]],
            [3 => [0 => $this->createExportRow(3)]],
            [],
        ]);
        $this->rowCustomizer->method('addData')->willReturnCallback(
            static function (array $data, $productId): array {
                if ($productId < 3) {
                    throw new \RuntimeException('Malformed product data');
                }
                return $data;
            }
        );
        $this->logger->expects($this->exactly(2))->method('critical');
        $this->writer->expects($this->once())->method('writeRow')
            ->with($this->callback(static fn (array $row): bool => $row['sku'] === 'sku-3'));
        $this->writer->expects($this->once())->method('getContents')->willReturn('completed');

        $this->assertSame('completed', $product->export());
    }

    public function testExportLogsWhenPopulatedBatchDoesNotAdvanceCursor(): void
    {
        $product = $this->createBatchExportProduct([[1 => [0 => $this->createExportRow(1)]]], true);
        $this->rowCustomizer->method('addData')->willReturnArgument(0);
        $this->writer->expects($this->once())->method('writeRow');
        $this->writer->expects($this->once())->method('getContents')->willReturn('partial');
        $this->logger->expects($this->once())->method('critical')->with(
            'Product export pagination did not advance. Last processed entity_id: 0'
        );

        $this->assertSame('partial', $product->export());
    }

    public function testExportLogsWriterFailureAndReturnsContents(): void
    {
        $product = $this->createBatchExportProduct([[1 => [0 => $this->createExportRow(1)]], []]);
        $this->rowCustomizer->method('addData')->willReturnArgument(0);
        $failure = new \RuntimeException('Cannot write export row');
        $this->writer->expects($this->once())->method('writeRow')->willThrowException($failure);
        $this->writer->expects($this->once())->method('getContents')->willReturn('partial');
        $this->logger->expects($this->once())->method('critical')->with($failure);

        $this->assertSame('partial', $product->export());
    }

    public function testExportLogsBatchPreparationFailureAndReturnsContents(): void
    {
        $product = $this->createBatchExportProduct([[1 => [0 => $this->createExportRow(1)]]]);
        $failure = new \RuntimeException('Cannot prepare export rows');
        $this->rowCustomizer->method('prepareData')->willThrowException($failure);
        $this->writer->expects($this->never())->method('writeRow');
        $this->writer->expects($this->once())->method('getContents')->willReturn('partial');
        $messages = [];
        $this->logger->expects($this->exactly(2))->method('critical')
            ->willReturnCallback(static function ($message) use (&$messages): void {
                $messages[] = $message;
            });

        $this->assertSame('partial', $product->export());
        $this->assertSame($failure, $messages[0]);
        $this->assertSame(
            'Product export pagination did not advance. Last processed entity_id: 0',
            $messages[1]
        );
    }

    public function testExportRetainsEarlierStoreRowWhenLaterStoreConversionFails(): void
    {
        $invalidOption = new class {
            public function __toString(): string
            {
                throw new \RuntimeException('Malformed store option');
            }
        };
        $product = $this->createBatchExportProduct(
            [
                [1 => [0 => $this->createExportRow(1), 1 => $this->createExportRow(1, 1)],
                    2 => [0 => $this->createExportRow(2)]],
                [],
            ],
            false,
            ['customOptionsData' => [1 => [1 => [$invalidOption]]]]
        );
        $this->rowCustomizer->method('addData')->willReturnArgument(0);
        $this->logger->expects($this->once())->method('critical')
            ->with($this->isInstanceOf(\RuntimeException::class));
        $rows = [];
        $this->writer->expects($this->exactly(2))->method('writeRow')
            ->willReturnCallback(static function (array $row) use (&$rows): void {
                $rows[] = $row;
            });
        $this->writer->expects($this->once())->method('getContents')->willReturn('completed');

        $this->assertSame('completed', $product->export());
        $this->assertSame(['sku-1', 'sku-2'], array_column($rows, 'sku'));
    }

    private function createExportRow(int $productId, int $storeId = Store::DEFAULT_STORE_ID): array
    {
        return [
            'product_id' => $productId,
            'product_link_id' => $productId,
            'store_id' => $storeId,
            Product::COL_SKU => 'sku-' . $productId,
            Product::COL_TYPE => 'simple',
            Product::COL_ATTR_SET => 'Default',
            'name' => 'Product ' . $productId,
        ];
    }

    private function createBatchExportProduct(
        array $batches,
        bool $stallCursor = false,
        array $multiRawData = []
    ): Product {
        $product = $this->createPartialMock(Product::class, [
            'getWriter', 'getItemsPerPage', 'getProductEntityLinkField',
            'collectRawData', 'collectMultirawData', 'prepareCatalogInventory', 'setHeaderColumns',
            '_getExportAttrCodes', 'getAttributeCollection', 'filterAttributeCollection', '_getHeaderColumns',
        ]);
        $product->method('getWriter')->willReturn($this->writer);
        $product->method('getItemsPerPage')->willReturn(2);
        $product->method('getProductEntityLinkField')->willReturn('entity_id');
        $product->method('_getHeaderColumns')->willReturn(['sku', 'name']);
        $product->method('_getExportAttrCodes')->willReturn([]);
        $product->method('prepareCatalogInventory')->willReturn([]);
        $product->method('collectMultirawData')->willReturn(array_replace(
            ['customOptionsData' => [], 'rowCategories' => [], 'rowWebsites' => []],
            $multiRawData
        ));
        $attributes = $this->createMock(AttributeCollection::class);
        $attributes->method('getIterator')->willReturn(new \ArrayIterator([]));
        $product->method('getAttributeCollection')->willReturn($attributes);
        $product->method('filterAttributeCollection')->willReturn($attributes);
        $batchIndex = 0;
        $product->expects($this->exactly(count($batches)))->method('collectRawData')->willReturnCallback(
            function () use ($product, $batches, &$batchIndex, $stallCursor): array {
                $batch = $batches[$batchIndex++];
                $ids = array_keys($batch);
                $this->setPropertyValue($product, 'currentPageEntityIds', $ids);
                $items = [];
                foreach ($ids as $id) {
                    $items[$id] = [new DataObject(['entity_id' => $id])];
                }
                // Exercise the real cursor initialization and commit around isolated row fixtures.
                (new \ReflectionMethod(Product::class, 'initializeBatchState'))->invoke($product, $items);
                if ($stallCursor && $batch) {
                    $this->setPropertyValue($product, 'pendingLastProcessedLinkFieldValue', 0);
                }
                return $batch;
            }
        );
        $select = $this->createMock(Select::class);
        $select->method('where')->willReturnSelf();
        $connection = $this->createMock(AdapterInterface::class);
        $connection->method('quoteIdentifier')->willReturnArgument(0);
        $collection = $this->createMock(ProductCollection::class);
        $collection->method('getSelect')->willReturn($select);
        $collection->method('getConnection')->willReturn($connection);
        $this->collection->method('create')->willReturn($collection);
        $this->filter->method('filter')->willReturn($collection);
        $this->linkTypeProvider->method('getLinkTypes')->willReturn([]);
        foreach ([
            'rowCustomizer' => $this->rowCustomizer,
            '_logger' => $this->logger,
            '_linkTypeProvider' => $this->linkTypeProvider,
            '_entityCollectionFactory' => $this->collection,
            'filter' => $this->filter,
            '_storeIdToCode' => [0 => 'admin', 1 => 'store'],
        ] as $name => $value) {
            $this->setPropertyValue($product, $name, $value);
        }

        return $product;
    }

    protected function tearDown(): void
    {
        unset($this->object);
    }

    /**
     * Get any object property value.
     *
     * @param $object
     * @param $property
     * @return mixed
     */
    protected function getPropertyValue($object, $property)
    {
        $reflectionProperty = $this->getReflectionProperty($object, $property);

        return $reflectionProperty->getValue($object);
    }

    /**
     * Set object property value.
     *
     * @param $object
     * @param $property
     * @param $value
     */
    protected function setPropertyValue(&$object, $property, $value)
    {
        $reflectionProperty = $this->getReflectionProperty($object, $property);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * @param object $object
     * @param string $property
     * @return \ReflectionProperty
     */
    private function getReflectionProperty(object $object, string $property): \ReflectionProperty
    {
        $reflection = new \ReflectionClass(get_class($object));
        while ($reflection !== false) {
            if ($reflection->hasProperty($property)) {
                return $reflection->getProperty($property);
            }
            $reflection = $reflection->getParentClass();
        }

        throw new \ReflectionException(sprintf('Property %s::$%s does not exist', get_class($object), $property));
    }

    public function testGetItemsPerPageDecreasesWithMoreAttributeOptions(): void
    {
        $memoryLimit = '3G';
        $this->assertMemoryLimitCanRunScenario($memoryLimit);

        $noOptions = [];
        $mediumOptions = $this->buildOptions(2501);
        $manyOptions = $this->buildOptions(5001);

        $withoutOptions = $this->invokeGetItemsPerPage($memoryLimit, $noOptions);
        $withMediumOptions = $this->invokeGetItemsPerPage($memoryLimit, $mediumOptions);
        $withManyOptions = $this->invokeGetItemsPerPage($memoryLimit, $manyOptions);

        $this->assertGreaterThanOrEqual($withMediumOptions, $withoutOptions);
        $this->assertGreaterThanOrEqual($withManyOptions, $withMediumOptions);
    }

    public function testGetItemsPerPageDoesNotDecreaseWithinSameMemoryBand(): void
    {
        $options = $this->buildOptions(5001);
        $this->assertMemoryLimitCanRunScenario('1G');

        $result512m = $this->invokeGetItemsPerPage('512M', $options);
        $result768m = $this->invokeGetItemsPerPage('768M', $options);
        $result1g = $this->invokeGetItemsPerPage('1G', $options);

        $this->assertGreaterThanOrEqual($result512m, $result768m);
        $this->assertGreaterThanOrEqual($result768m, $result1g);
    }

    public function testUnlimitedPaginationIsIndependentOfStoreCount(): void
    {
        $singleStore = $this->invokeGetItemsPerPage('-1', [], 1);
        $multipleStores = $this->invokeGetItemsPerPage('-1', [], 9);

        $this->assertGreaterThan(0, $singleStore);
        $this->assertSame($singleStore, $multipleStores);
    }

    #[DataProvider('candidateIdentitiesProvider')]
    public function testCandidatePageIncludesEveryStoreWithoutHydration(array $matches, array $expected): void
    {
        $collections = [];
        foreach ($matches as $storeId => $identities) {
            $collection = $this->createMock(ExportCollection::class);
            $collection->expects($this->once())->method('setStoreId')->with($storeId)->willReturnSelf();
            $collection->expects($this->once())->method('getExportCandidateIdentities')
                ->with(2, $this->anything())->willReturn($identities);
            $collection->expects($this->never())->method('load');
            $collections[] = $collection;
        }
        $this->product->method('_getEntityCollection')->willReturnOnConsecutiveCalls(...$collections);
        $this->setPropertyValue($this->product, '_storeIdToCode', [0 => 'admin', 1 => 'first', 2 => 'later']);
        $this->setPropertyValue($this->product, 'currentIterationItemsPerPage', 2);

        $method = new \ReflectionMethod(Product::class, 'selectCandidateIdentities');
        $this->assertSame($expected, $method->invoke($this->product));
        $this->assertSame(
            array_keys($expected),
            $this->getReflectionProperty($this->product, 'currentPageEntityIds')->getValue($this->product)
        );
        $this->assertSame(
            $collections,
            $this->getReflectionProperty($this->product, 'currentStoreCollections')->getValue($this->product)
        );
    }

    /**
     * @return array<string, array{array<int, array<int, int>>, array<int, int>}>
     */
    public static function candidateIdentitiesProvider(): array
    {
        return [
            'later-store-only candidates precede default matches' => [
                [[800 => 8, 900 => 9], [200 => 2], [100 => 1, 500 => 5]],
                [100 => 1, 200 => 2],
            ],
            'default store excludes every candidate' => [
                [[], [200 => 2], [100 => 1]],
                [100 => 1, 200 => 2],
            ],
            'overlapping candidates are deduplicated across identifier gaps' => [
                [[100 => 1], [100 => 1, 500 => 5], [300 => 3]],
                [100 => 1, 300 => 3],
            ],
            'all stores exhausted' => [
                [[], [], []],
                [],
            ],
        ];
    }

    public function testCoreLoaderUsesIncrementalPathAndLoadingOverridesUseBufferedPath(): void
    {
        $method = new \ReflectionMethod(Product::class, 'canLoadIncrementally');
        $product = (new \ReflectionClass(Product::class))->newInstanceWithoutConstructor();
        $this->setPropertyValue($product, '_entityCollection', $this->createMock(ExportCollection::class));
        $this->assertTrue($method->invoke($product));

        foreach (['loadCollection', 'collectRawData', 'collectMultirawData'] as $hook) {
            $override = $this->createPartialMock(Product::class, [$hook]);
            $this->assertFalse($method->invoke($override), $hook);
        }
    }

    public function testUnlimitedPaginationStillAccountsForAttributeOptions(): void
    {
        $withoutOptions = $this->invokeGetItemsPerPage('-1', [], 9);
        $withManyOptions = $this->invokeGetItemsPerPage('-1', $this->buildOptions(5001), 9);

        $this->assertGreaterThan(0, $withManyOptions);
        $this->assertLessThan($withoutOptions, $withManyOptions);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testGetItemsPerPagePreservesFiniteMemorySizing(): void
    {
        $this->assertMemoryLimitCanRunScenario('8G');
        $this->setPropertyValue($this->product, 'estimatedMemoryPerProduct', 3000000.0);

        foreach (['256M', '512M', '1G', '2G', '3G', '4G', '8G'] as $memoryLimit) {
            $singleStore = $this->invokeGetItemsPerPage($memoryLimit, [], 1);

            foreach ([9, 60] as $storeCount) {
                $this->assertSame($singleStore, $this->invokeGetItemsPerPage($memoryLimit, [], $storeCount));
            }
        }
    }

    /**
     * Calculate pagination with independent memory and attribute-option inputs.
     *
     * @param string $memoryLimit
     * @param array $options
     * @param int $storeCount
     * @return int
     * @throws \ReflectionException
     */
    private function invokeGetItemsPerPage(string $memoryLimit, array $options, int $storeCount = 1): int
    {
        $reflection = new \ReflectionClass(get_class($this->object));
        $method = $reflection->getMethod('getItemsPerPage');
        $currentMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', $memoryLimit);
        try {
            $this->setPropertyValue($this->product, '_itemsPerPage', null);
            $this->setPropertyValue($this->product, 'cachedMemoryLimitForPagination', null);
            $this->setPropertyValue($this->product, 'cachedMaxAttributeValues', count($options));
            $this->setPropertyValue($this->product, 'itemsPerPageCalculationIteration', 0);
            $this->setPropertyValue($this->product, 'currentMemoryUsage', 0);
            $this->setPropertyValue($this->product, 'currentMaxAllowedMemoryUsage', 0);
            $this->setPropertyValue($this->product, '_attributeValues', ['test_attribute' => $options]);
            $this->setPropertyValue(
                $this->product,
                '_storeIdToCode',
                array_map(static fn(int $storeId): string => 'store_' . $storeId, range(0, $storeCount - 1))
            );
            return (int)$method->invoke($this->product);
        } finally {
            ini_set('memory_limit', $currentMemoryLimit);
        }
    }

    /**
     * @param int $count
     * @return array
     */
    private function buildOptions(int $count): array
    {
        $options = [];
        for ($i = 0; $i < $count; $i++) {
            $options[] = ['label' => 'Option ' . $i, 'value' => $i];
        }

        return $options;
    }

    /**
     * @param string $requiredMemoryLimit
     * @return void
     */
    private function assertMemoryLimitCanRunScenario(string $requiredMemoryLimit): void
    {
        $currentMemoryLimit = (string)ini_get('memory_limit');
        if ($currentMemoryLimit === '-1') {
            return;
        }

        if ($this->memoryLimitToBytes($currentMemoryLimit) < $this->memoryLimitToBytes($requiredMemoryLimit)) {
            $this->markTestSkipped('Memory limit is too low for this test');
        }
    }

    /**
     * @param string $memoryLimit
     * @return int
     */
    private function memoryLimitToBytes(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        if ($memoryLimit === '-1') {
            return PHP_INT_MAX;
        }

        $value = (int)$memoryLimit;
        $suffix = strtolower(substr($memoryLimit, -1));
        switch ($suffix) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }
}
