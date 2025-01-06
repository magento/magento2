<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\CatalogImportExport\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\Product\Type\Factory;
use Magento\CatalogImportExport\Model\Export\ProductFilterInterface;
use Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite;
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
        $this->abstractCollection = $this->getMockForAbstractClass(
            AbstractCollection::class,
            [],
            '',
            false,
            true,
            true,
            [
                'count',
                'setOrder',
                'setStoreId',
                'getCurPage',
                'getLastPageNumber',
            ]
        );
        $this->exportConfig = $this->createMock(\Magento\ImportExport\Model\Export\Config::class);

        $this->productFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\ProductFactory::class
        )->disableOriginalConstructor()
            ->addMethods(['getTypeId'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->attrSetColFactory = $this->getMockBuilder(AttributeSetCollectionFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['setEntityTypeFilter'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->categoryColFactory = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->disableOriginalConstructor()->addMethods(['addNameToResult'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->itemFactory = $this->createMock(\Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory::class);
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
        $page = 1;
        $itemsPerPage = 10;

        $this->product->expects($this->once())->method('getWriter')->willReturn($this->writer);
        $this->product
            ->expects($this->exactly(1))
            ->method('_getEntityCollection')
            ->willReturn($this->abstractCollection);
        $this->product->expects($this->once())->method('_prepareEntityCollection')->with($this->abstractCollection);
        $this->product->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->product->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollection->expects($this->once())->method('setOrder')->with('entity_id', 'asc');
        $this->abstractCollection->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);

        $this->abstractCollection->expects($this->once())->method('count')->willReturn(0);

        $this->abstractCollection->expects($this->never())->method('getCurPage');
        $this->abstractCollection->expects($this->never())->method('getLastPageNumber');
        $this->product->expects($this->never())->method('_getHeaderColumns');
        $this->writer->expects($this->never())->method('setHeaderCols');
        $this->writer->expects($this->never())->method('writeRow');
        $this->product->expects($this->never())->method('getExportData');
        $this->product->expects($this->never())->method('_customFieldsMapping');

        $this->writer->expects($this->once())->method('getContents');

        $this->product->export();
    }

    public function testExportCurPageEqualToLastBreakInternalCalls()
    {
        $curPage = $lastPage = $page = 1;
        $itemsPerPage = 10;

        $this->product->expects($this->once())->method('getWriter')->willReturn($this->writer);
        $this->product
            ->expects($this->exactly(1))
            ->method('_getEntityCollection')
            ->willReturn($this->abstractCollection);
        $this->product->expects($this->once())->method('_prepareEntityCollection')->with($this->abstractCollection);
        $this->product->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->product->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollection->expects($this->once())->method('setOrder')->with('entity_id', 'asc');
        $this->abstractCollection->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);

        $this->abstractCollection->expects($this->once())->method('count')->willReturn(1);

        $this->abstractCollection->expects($this->once())->method('getCurPage')->willReturn($curPage);
        $this->abstractCollection->expects($this->once())->method('getLastPageNumber')->willReturn($lastPage);
        $headers = ['headers'];
        $this->product->expects($this->once())->method('_getHeaderColumns')->willReturn($headers);
        $this->writer->expects($this->once())->method('setHeaderCols')->with($headers);
        $row = 'value';
        $data = [$row];
        $this->product->expects($this->once())->method('getExportData')->willReturn($data);
        $customFieldsMappingResult = ['result'];
        $this->product
            ->expects($this->once())
            ->method('_customFieldsMapping')
            ->with($row)
            ->willReturn($customFieldsMappingResult);
        $this->writer->expects($this->once())->method('writeRow')->with($customFieldsMappingResult);

        $this->writer->expects($this->once())->method('getContents');

        $this->product->export();
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
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);

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
        $reflection = new \ReflectionClass(get_class($object));
        $reflectionProperty = $reflection->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);

        return $object;
    }

    /**
     * Test for getItemsPerPage and adjustItemsPerPageByAttributeOptions methods
     *
     * @return void
     * @throws \ReflectionException
     *
     * @dataProvider getItemsPerPageDataProvider
     */
    public function testGetItemsPerPage($scenarios)
    {

        $reflection = new \ReflectionClass(get_class($this->object));
        $method = $reflection->getMethod('getItemsPerPage');

        $currentMemoryLimit = ini_get('memory_limit');

        foreach ($scenarios as $scenario) {
            if ($currentMemoryLimit !== "-1" && $currentMemoryLimit < $scenario['memory_limit']) {
                $this->markTestSkipped('Memory limit is too low for this test');
            }
            ini_set('memory_limit', $scenario['memory_limit']);
            $this->setPropertyValue(
                $this->product,
                '_attributeValues',
                ['test_attribute' => $scenario['options'] ?? []]
            );
            $result = $method->invoke($this->product);
            $this->assertLessThanOrEqual(
                $scenario['expected_items_per_page'],
                $result,
                'Memory limit: ' . $scenario['memory_limit'] . ' Options count: ' . count($scenario['options'])
            );
            $this->setPropertyValue($this->product, '_itemsPerPage', null);
            ini_set('memory_limit', $currentMemoryLimit);
        }
    }

    /**
     * @return array[]
     */
    public static function getItemsPerPageDataProvider(): array
    {
        $options = [];

        // Simulate different scenarios without attribute options
        $scenarios['Attribute options: ' . count($options)] = [[
            [
                'memory_limit' => '4G',
                'options' => $options,
                'expected_items_per_page' => 5000,
            ],
            [
                'memory_limit' => '3G',
                'options' => $options,
                'expected_items_per_page' => 5000,
            ],
            [
                'memory_limit' => '2G',
                'options' => $options,
                'expected_items_per_page' => 5000,
            ]
        ]];

        $options = [];
        for ($i = 0; $i <= 5000; $i++) {
            $options[] = ['label' => 'Option ' . $i, 'value' => $i];
        }

        // Simulate different scenarios with attribute options over 5000
        $scenarios['Attribute options: ' . count($options)] = [[
            [
                'memory_limit' => '4G',
                'options' => $options,
                'expected_items_per_page' => 1800,
            ],
            [
                'memory_limit' => '3G',
                'options' => $options,
                'expected_items_per_page' => 1500,
            ],
            [
                'memory_limit' => '2G',
                'options' => $options,
                'expected_items_per_page' => 1000,
            ]
        ]];

        $options = [];
        for ($i = 0; $i <= 2500; $i++) {
            $options[] = ['label' => 'Option ' . $i, 'value' => $i];
        }

        // Simulate different scenarios with attribute options over 2500
        $scenarios['Attribute options: ' . count($options)] = [[
            [
                'memory_limit' => '4G',
                'options' => $options,
                'expected_items_per_page' => 3500,
            ],
            [
                'memory_limit' => '3G',
                'options' => $options,
                'expected_items_per_page' => 3000,
            ],
            [
                'memory_limit' => '2G',
                'options' => $options,
                'expected_items_per_page' => 2500,
            ]
        ]];

        return $scenarios;
    }
}
