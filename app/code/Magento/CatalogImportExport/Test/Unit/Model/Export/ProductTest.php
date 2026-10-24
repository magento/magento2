<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogImportExport\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\Product\Type\Factory;
use Magento\CatalogImportExport\Model\Export\ProductFilterInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as AttributeSetCollection;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
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

    public function testGetItemsPerPageIncreasesWithMoreMemoryForHeavyAttributes(): void
    {
        $options = $this->buildOptions(5001);
        $this->assertMemoryLimitCanRunScenario('4G');

        $result2g = $this->invokeGetItemsPerPage('2G', $options);
        $result3g = $this->invokeGetItemsPerPage('3G', $options);
        $result4g = $this->invokeGetItemsPerPage('4G', $options);

        $this->assertGreaterThanOrEqual($result2g, $result3g);
        $this->assertGreaterThanOrEqual($result3g, $result4g);
    }

    /**
     * @param string $memoryLimit
     * @param array $options
     * @return int
     * @throws \ReflectionException
     */
    private function invokeGetItemsPerPage(string $memoryLimit, array $options): int
    {
        $reflection = new \ReflectionClass(get_class($this->object));
        $method = $reflection->getMethod('getItemsPerPage');
        $currentMemoryLimit = ini_get('memory_limit');
        ini_set('memory_limit', $memoryLimit);
        $this->setPropertyValue($this->product, '_itemsPerPage', null);
        $this->setPropertyValue($this->product, 'itemsPerPageCalculationIteration', 0);
        $this->setPropertyValue($this->product, 'currentMemoryUsage', 0);
        $this->setPropertyValue($this->product, 'currentMaxAllowedMemoryUsage', 0);
        $this->setPropertyValue($this->product, '_attributeValues', ['test_attribute' => $options]);
        $result = (int)$method->invoke($this->product);
        ini_set('memory_limit', $currentMemoryLimit);
        return $result;
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
