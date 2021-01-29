<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Export;

use Magento\Store\Model\Store;

/**
 * @SuppressWarnings(PHPMD)
 */
class AdvancedPricingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeDate;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $collection;

    /**
     * @var \Magento\Eav\Model\Entity\Collection\AbstractCollection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $abstractCollection;

    /**
     * @var \Magento\ImportExport\Model\Export\ConfigInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $exportConfig;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\ProductFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attrSetColFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $categoryColFactory;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $itemFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $optionColFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $attributeColFactory;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\Product\Type\Factory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $typeFactory;

    /**
     * @var \Magento\Catalog\Model\Product\LinkTypeProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $linkTypeProvider;

    /**
     * @var \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $rowCustomizer;

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product\StoreResolver|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeResolver;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $groupRepository;

    /**
     * @var \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter| \PHPUnit\Framework\MockObject\MockObject
     */
    protected $writer;

    /**
     * @var \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $advancedPricing;

    /**
     * @var StubProduct|\Magento\CatalogImportExport\Model\Export\Product
     */
    protected $object;

    /**
     * Set Up
     */
    protected function setUp(): void
    {
        $this->localeDate = $this->createMock(\Magento\Framework\Stdlib\DateTime\Timezone::class);
        $this->config = $this->createPartialMock(\Magento\Eav\Model\Config::class, ['getEntityType']);
        $type = $this->createMock(\Magento\Eav\Model\Entity\Type::class);
        $this->config->expects($this->once())->method('getEntityType')->willReturn($type);
        $this->resource = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->storeManager = $this->createMock(\Magento\Store\Model\StoreManager::class);
        $this->logger = $this->createMock(\Magento\Framework\Logger\Monolog::class);
        $this->collection = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);
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
        $this->exportConfig = $this->createMock(\Magento\ImportExport\Model\Export\Config::class);
        $this->productFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\ProductFactory::class,
            [
                'create',
                'getTypeId',
            ]
        );
        $this->attrSetColFactory = $this->createPartialMock(
            \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory::class,
            [
                'create',
                'setEntityTypeFilter',
            ]
        );
        $this->categoryColFactory = $this->createPartialMock(
            \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory::class,
            [
                'create',
                'addNameToResult',
            ]
        );
        $this->itemFactory = $this->createMock(\Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory::class);
        $this->optionColFactory = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory::class
        );
        $this->attributeColFactory = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory::class
        );
        $this->typeFactory = $this->createMock(\Magento\CatalogImportExport\Model\Export\Product\Type\Factory::class);
        $this->linkTypeProvider = $this->createMock(\Magento\Catalog\Model\Product\LinkTypeProvider::class);
        $this->rowCustomizer = $this->createMock(
            \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite::class
        );
        $this->storeResolver = $this->createMock(
            \Magento\CatalogImportExport\Model\Import\Product\StoreResolver::class
        );
        $this->groupRepository = $this->createMock(\Magento\Customer\Api\GroupRepositoryInterface::class);
        $this->writer = $this->createPartialMock(
            \Magento\ImportExport\Model\Export\Adapter\AbstractAdapter::class,
            [
            'setHeaderCols',
            'writeRow',
            'getContents',
            ]
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
            '_getWebsiteCode',
            '_getCustomerGroupById',
            'correctExportData'
        ]);
        $this->advancedPricing = $this->getMockBuilder(
            \Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing::class
        )
            ->setMethods($mockMethods)
            ->disableOriginalConstructor()
            ->getMock();
        foreach ($constructorMethods as $method) {
            $this->advancedPricing->expects($this->once())->method($method)->willReturnSelf();
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

    /**
     * Test export with zero condition
     */
    public function testExportZeroConditionCalls()
    {
        $page = 1;
        $itemsPerPage = 10;

        $this->advancedPricing->expects($this->once())->method('getWriter')->willReturn($this->writer);
        $this->advancedPricing
            ->expects($this->exactly(1))
            ->method('_getEntityCollection')
            ->willReturn($this->abstractCollection);
        $this->advancedPricing
            ->expects($this->once())
            ->method('_prepareEntityCollection')
            ->with($this->abstractCollection);
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

    /**
     * Test export for current page
     */
    public function testExportCurrentPageCalls()
    {
        $curPage = $lastPage = $page = 1;
        $itemsPerPage = 10;
        $this->advancedPricing->expects($this->once())->method('getWriter')->willReturn($this->writer);
        $this->advancedPricing
            ->expects($this->exactly(1))
            ->method('_getEntityCollection')
            ->willReturn($this->abstractCollection);
        $this->advancedPricing
            ->expects($this->once())
            ->method('_prepareEntityCollection')
            ->with($this->abstractCollection);
        $this->advancedPricing->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->advancedPricing->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollection->expects($this->once())->method('setOrder')->with('has_options', 'asc');
        $this->abstractCollection->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);
        $this->abstractCollection->expects($this->once())->method('count')->willReturn(1);
        $this->abstractCollection->expects($this->once())->method('getCurPage')->willReturn($curPage);
        $this->abstractCollection->expects($this->once())->method('getLastPageNumber')->willReturn($lastPage);
        $headers = ['headers'];
        $this->advancedPricing->expects($this->any())->method('_getHeaderColumns')->willReturn($headers);
        $this->writer->expects($this->any())->method('setHeaderCols')->with($headers);
        $webSite = 'All Websites [USD]';
        $userGroup = 'General';
        $this->advancedPricing->expects($this->any())->method('_getWebsiteCode')->willReturn($webSite);
        $this->advancedPricing->expects($this->any())->method('_getCustomerGroupById')->willReturn($userGroup);
        $data = [
            [
                'sku' => 'simpletest',
                'tier_price_website' => $webSite,
                'tier_price_customer_group' => $userGroup,
                'tier_price_qty' => '2',
                'tier_price' => '23',
            ]
        ];
        $this->advancedPricing->expects($this->once())->method('getExportData')->willReturn($data);
        $exportData = [
            'sku' => 'simpletest',
            'tier_price_website' => $webSite,
            'tier_price_customer_group' => $userGroup,
            'tier_price_qty' => '2',
            'tier_price' => '23',
        ];
        $this->advancedPricing
            ->expects($this->any())
            ->method('correctExportData')
            ->willReturn($exportData);
        $this->writer->expects($this->once())->method('writeRow')->with($exportData);
        $this->writer->expects($this->once())->method('getContents');
        $this->advancedPricing->export();
    }

    /**
     * tearDown
     */
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
     * @throws \ReflectionException
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
     * @return mixed
     * @throws \ReflectionException
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
