<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use \Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD)
 */
class ExportTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Catalog\Model\Resource\Product\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Catalog\Model\Resource\ProductFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productFactory;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Category\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryColFactory;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock\ItemFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Option\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $optionColFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
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

    protected $storeResolver;

    protected $groupRepository;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter| \PHPUnit_Framework_MockObject_MockObject
     */
    protected $writer;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $advancedPricing;

    protected $_entityTypeCode;


    /**
     * @var StubProduct|\Magento\CatalogImportExport\Model\Export\Product
     */
    protected $object;

    protected function setUp()
    {
        $this->localeDate = $this->getMock(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            [],
            [],
            '',
            false
        );

        $this->config = $this->getMock(
            'Magento\Eav\Model\Config',
            ['getEntityType'],
            [],
            '',
            false
        );
        $type = $this->getMock(
            '\Magento\Eav\Model\Entity\Type',
            [],
            [],
            '',
            false
        );
        $this->config->expects($this->once())->method('getEntityType')->willReturn($type);

        $this->resource = $this->getMock(
            'Magento\Framework\App\Resource',
            [],
            [],
            '',
            false
        );

        $this->storeManager = $this->getMock(
            'Magento\Store\Model\StoreManager',
            [],
            [],
            '',
            false
        );
        $this->logger = $this->getMock(
            'Magento\Framework\Logger\Monolog',
            [],
            [],
            '',
            false
        );

        $this->collection = $this->getMock(
            '\Magento\Catalog\Model\Resource\Product\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->abstractCollection = $this->getMockForAbstractClass(
            '\Magento\Eav\Model\Entity\Collection\AbstractCollection',
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
            'Magento\ImportExport\Model\Export\Config',
            [],
            [],
            '',
            false
        );

        $this->productFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\ProductFactory',
            [
                'create',
                'getTypeId',
            ],
            [],
            '',
            false
        );

        $this->attrSetColFactory = $this->getMock(
            'Magento\Eav\Model\Resource\Entity\Attribute\Set\CollectionFactory',
            [
                'create',
                'setEntityTypeFilter',
            ],
            [],
            '',
            false
        );

        $this->categoryColFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Category\CollectionFactory',
            [
                'create',
                'addNameToResult',
            ],
            [],
            '',
            false
        );

        $this->itemFactory = $this->getMock(
            'Magento\CatalogInventory\Model\Resource\Stock\ItemFactory',
            [],
            [],
            '',
            false
        );
        $this->optionColFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Option\CollectionFactory',
            [],
            [],
            '',
            false
        );

        $this->attributeColFactory = $this->getMock(
            'Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->typeFactory = $this->getMock(
            'Magento\CatalogImportExport\Model\Export\Product\Type\Factory',
            [],
            [],
            '',
            false
        );

        $this->linkTypeProvider = $this->getMock(
            'Magento\Catalog\Model\Product\LinkTypeProvider',
            [],
            [],
            '',
            false
        );
        $this->rowCustomizer = $this->getMock(
            'Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite',
            [],
            [],
            '',
            false
        );
        $this->storeResolver = $this->getMock(
            '\Magento\CatalogImportExport\Model\Import\Product\StoreResolver',
            [],
            [],
            '',
            false
        );
        $this->groupRepository = $this->getMock(
            '\Magento\Customer\Api\GroupRepositoryInterface',
            [],
            [],
            '',
            false
        );

        $this->writer = $this->getMock(
            'Magento\ImportExport\Model\Export\Adapter\AbstractAdapter',
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
        $this->advancedPricing = $this->getMock(
            'Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing',
            $mockMethods,
            [],
            '',
            false
        );

        foreach ($constructorMethods as $method) {
            $this->advancedPricing->expects($this->once())->method($method)->will($this->returnSelf());
        }

        $this->advancedPricing->__construct(
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
            $this->storeResolver,
            $this->groupRepository
        );
    }

    public function testExportCountZeroBreakInternalCalls()
    {
        $page = 1;
        $itemsPerPage = 10;

        $this->advancedPricing->expects($this->once())->method('getWriter')->willReturn($this->writer);
        $this->advancedPricing->expects($this->exactly(1))->method('_getEntityCollection')->willReturn($this->abstractCollection);
        $this->advancedPricing->expects($this->once())->method('_prepareEntityCollection')->with($this->abstractCollection);
        $this->advancedPricing->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->advancedPricing->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollection->expects($this->once())->method('setOrder')->with('has_options', 'asc');
        $this->abstractCollection->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);

        $this->abstractCollection->expects($this->once())->method('count')->willReturn(0);

        $this->abstractCollection->expects($this->never())->method('getCurPage');
        $this->abstractCollection->expects($this->never())->method('getLastPageNumber');
        $this->advancedPricing->expects($this->never())->method('_getHeaderColumns');
        $this->writer->expects($this->never())->method('setHeaderCols');
        $this->writer->expects($this->never())->method('writeRow');
        $this->advancedPricing->expects($this->never())->method('getExportData');
        $this->advancedPricing->expects($this->never())->method('_customFieldsMapping');

        $this->writer->expects($this->once())->method('getContents');

        $this->advancedPricing->export();
    }

    public function testExportCurPageEqualToLastBreakInternalCalls()
    {
        $curPage = $lastPage = $page = 1;
        $itemsPerPage = 10;

        $this->advancedPricing->expects($this->once())->method('getWriter')->willReturn($this->writer);
        $this->advancedPricing->expects($this->exactly(1))->method('_getEntityCollection')->willReturn($this->abstractCollection);
        $this->advancedPricing->expects($this->once())->method('_prepareEntityCollection')->with($this->abstractCollection);
        $this->advancedPricing->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->advancedPricing->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollection->expects($this->once())->method('setOrder')->with('has_options', 'asc');
        $this->abstractCollection->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);

        $this->abstractCollection->expects($this->once())->method('count')->willReturn(1);

        $this->abstractCollection->expects($this->once())->method('getCurPage')->willReturn($curPage);
        $this->abstractCollection->expects($this->once())->method('getLastPageNumber')->willReturn($lastPage);
        $headers = ['headers'];
        $this->advancedPricing->expects($this->once())->method('_getHeaderColumns')->willReturn($headers);
        $this->writer->expects($this->once())->method('setHeaderCols')->with($headers);
        $data = [
            ImportAdvancedPricing::COL_SKU => 'simpletest',
            ImportAdvancedPricing::COL_GROUP_PRICE_WEBSITE => '0',
            ImportAdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => '1',
            ImportAdvancedPricing::COL_GROUP_PRICE => '100',
            ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE => '0',
            ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => '2',
            ImportAdvancedPricing::COL_TIER_PRICE_QTY => '2',
            ImportAdvancedPricing::COL_TIER_PRICE => '23',
        ];
        $this->advancedPricing->expects($this->once())->method('getExportData')->willReturn($data);
        $webSite = 'All Websites [USD]';
        $userGroup = 'General';
        $this->advancedPricing->expects($this->once())->method('_getWebsiteCode')->willReturn($webSite);
        $this->advancedPricing->expects($this->once())->method('_getCustomerGroupById')->willReturn($userGroup);
        $exportData = [
            ImportAdvancedPricing::COL_SKU => 'simpletest',
            ImportAdvancedPricing::COL_GROUP_PRICE_WEBSITE => $webSite,
            ImportAdvancedPricing::COL_GROUP_PRICE_CUSTOMER_GROUP => $userGroup,
            ImportAdvancedPricing::COL_GROUP_PRICE => '100',
            ImportAdvancedPricing::COL_TIER_PRICE_WEBSITE => $webSite,
            ImportAdvancedPricing::COL_TIER_PRICE_CUSTOMER_GROUP => $userGroup,
            ImportAdvancedPricing::COL_TIER_PRICE_QTY => '2',
            ImportAdvancedPricing::COL_TIER_PRICE => '23',
        ];
        $this->writer->expects($this->once())->method('writeRow')->with($exportData);

        $this->writer->expects($this->once())->method('getContents');

        $this->advancedPricing->export();
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
