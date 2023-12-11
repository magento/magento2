<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Export;

use Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\ProductFactory;
use Magento\CatalogImportExport\Model\Export\Product;
use Magento\CatalogImportExport\Model\Export\Product\Type\Factory;
use Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite;
use Magento\CatalogImportExport\Model\Import\Product\StoreResolver;
use Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Eav\Model\Entity\Type;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
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
class AdvancedPricingTest extends TestCase
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
     * @var CollectionFactory|MockObject
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
     * @var ProductFactory|MockObject
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
     * @var ItemFactory|MockObject
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
     * @var StoreResolver|MockObject
     */
    protected $storeResolver;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    protected $groupRepository;

    /**
     * @var AbstractAdapter|MockObject
     */
    protected $writer;

    /**
     * @var AdvancedPricing|MockObject
     */
    protected $advancedPricing;

    /**
     * @var StubProduct|Product
     */
    protected $object;

    /**
     * Set Up
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
        $this->collection = $this->createMock(CollectionFactory::class);
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
        $this->productFactory = $this->getMockBuilder(ProductFactory::class)
            ->addMethods(['getTypeId'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attrSetColFactory = $this->getMockBuilder(AttributeSetCollectionFactory::class)
            ->addMethods(['setEntityTypeFilter'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryColFactory = $this->getMockBuilder(CategoryCollectionFactory::class)
            ->addMethods(['addNameToResult'])
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
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
        $this->storeResolver = $this->createMock(
            StoreResolver::class
        );
        $this->groupRepository = $this->getMockForAbstractClass(GroupRepositoryInterface::class);
        $this->writer = $this->createPartialMock(
            AbstractAdapter::class,
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
            AdvancedPricing::class
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
        $this->advancedPricing->method('_getHeaderColumns')->willReturn($headers);
        $this->writer->method('setHeaderCols')->with($headers);
        $webSite = 'All Websites [USD]';
        $userGroup = 'General';
        $this->advancedPricing->method('_getWebsiteCode')->willReturn($webSite);
        $this->advancedPricing->method('_getCustomerGroupById')->willReturn($userGroup);
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
