<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\CatalogImportExport\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\Product\Type\Factory;
use Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
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
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory|MockObject
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
        )->addMethods(['getTypeId'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->attrSetColFactory = $this->getMockBuilder(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class
        )->addMethods(['setEntityTypeFilter'])
            ->onlyMethods(['create'])
            ->getMock();

        $this->categoryColFactory = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class
        )->addMethods(['addNameToResult'])
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
            $this->rowCustomizer
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
}
