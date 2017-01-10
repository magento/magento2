<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogImportExport\Test\Unit\Model\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD)
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $abstractCollection;

    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $exportConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryColFactory;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionColFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeColFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product\Type\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $rowCustomizer;

    /**
     * @var \Magento\Framework\EntityManager\MetadataPool|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataPool;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $writer;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var StubProduct|\Magento\CatalogImportExport\Model\Export\Product
     */
    protected $object;

    protected function setUp()
    {
        $this->localeDate = $this->getMock(
            \Magento\Framework\Stdlib\DateTime\Timezone::class,
            [],
            [],
            '',
            false
        );

        $this->config = $this->getMock(
            \Magento\Eav\Model\Config::class,
            ['getEntityType'],
            [],
            '',
            false
        );
        $type = $this->getMock(
            \Magento\Eav\Model\Entity\Type::class,
            [],
            [],
            '',
            false
        );
        $this->config->expects($this->once())->method('getEntityType')->willReturn($type);

        $this->resource = $this->getMock(
            \Magento\Framework\App\ResourceConnection::class,
            [],
            [],
            '',
            false
        );

        $this->storeManager = $this->getMock(
            \Magento\Store\Model\StoreManager::class,
            [],
            [],
            '',
            false
        );
        $this->logger = $this->getMock(
            \Magento\Framework\Logger\Monolog::class,
            [],
            [],
            '',
            false
        );

        $this->collection = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class,
            [],
            [],
            '',
            false
        );
        $this->abstractCollection = $this->getMockForAbstractClass(
            \Magento\Eav\Model\Entity\Collection\AbstractCollection::class,
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
        $this->exportConfig = $this->getMock(
            \Magento\ImportExport\Model\Export\Config::class,
            [],
            [],
            '',
            false
        );

        $this->productFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\ProductFactory::class,
            [
                'create',
                'getTypeId',
            ],
            [],
            '',
            false
        );

        $this->attrSetColFactory = $this->getMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            [
                'create',
                'setEntityTypeFilter',
            ],
            [],
            '',
            false
        );

        $this->categoryColFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class,
            [
                'create',
                'addNameToResult',
            ],
            [],
            '',
            false
        );

        $this->itemFactory = $this->getMock(
            \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory::class,
            [],
            [],
            '',
            false
        );
        $this->optionColFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory::class,
            [],
            [],
            '',
            false
        );

        $this->attributeColFactory = $this->getMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class,
            [],
            [],
            '',
            false
        );
        $this->typeFactory = $this->getMock(
            \Magento\CatalogImportExport\Model\Export\Product\Type\Factory::class,
            [],
            [],
            '',
            false
        );

        $this->linkTypeProvider = $this->getMock(
            \Magento\Catalog\Model\Product\LinkTypeProvider::class,
            [],
            [],
            '',
            false
        );
        $this->rowCustomizer = $this->getMock(
            \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite::class,
            [],
            [],
            '',
            false
        );
        $this->metadataPool = $this->getMock(
            \Magento\Framework\EntityManager\MetadataPool::class,
            [],
            [],
            '',
            false
        );

        $this->writer = $this->getMock(
            \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter::class,
            [
                'setHeaderCols',
                'writeRow',
                'getContents',
            ],
            [],
            '',
            false
        );

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
            '_headerColumns',
            '_customFieldsMapping',
            'getItemsPerPage',
            'paginateCollection',
            '_getHeaderColumns',
        ]);
        $this->product = $this->getMock(
            \Magento\CatalogImportExport\Model\Export\Product::class,
            $mockMethods,
            [],
            '',
            false
        );

        foreach ($constructorMethods as $method) {
            $this->product->expects($this->once())->method($method)->will($this->returnSelf());
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
        $product = $this->getMock(
            \Magento\CatalogImportExport\Model\Export\Product::class,
            ['_customHeadersMapping'],
            [],
            '',
            false
        );
        $headerColumnsValue = ['headerColumns value'];
        $expectedResult = 'result';
        $this->setPropertyValue($product, '_headerColumns', $headerColumnsValue);
        $product
            ->expects($this->once())
            ->method('_customHeadersMapping')
            ->with($headerColumnsValue)
            ->willReturn($expectedResult);

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

    protected function tearDown()
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
