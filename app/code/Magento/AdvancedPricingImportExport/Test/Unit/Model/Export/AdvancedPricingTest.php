<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedPricingImportExport\Test\Unit\Model\Export;

use Hoa\Iterator\Mock;
use Magento\AdvancedPricingImportExport\Model\Export\AdvancedPricing;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Option\CollectionFactory as ProductOptionCollectionFactory;
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
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttibuteSetCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\ImportExport\Model\Export\Config as ExportConfig;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AdvancedPricingTest extends TestCase
{
    /**
     * @var Timezone|Mock
     */
    private $localeDateMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceMock;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManagerMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ProductCollectionFactory|MockObject
     */
    private $collectionFactoryMock;

    /**
     * @var AbstractCollection|MockObject
     */
    private $abstractCollectionMock;

    /**
     * @var ConfigInterface|MockObject
     */
    private $exportConfigMock;

    /**
     * @var ProductFactory|MockObject
     */
    private $productFactoryMock;

    /**
     * @var AttibuteSetCollectionFactory|MockObject
     */
    private $attributeSetCollectionFactoryMock;

    /**
     * @var CategoryCollectionFactory|MockObject
     */
    private $categoryColFactoryMock;

    /**
     * @var ItemFactory|MockObject
     */
    private $itemFactoryMock;

    /**
     * @var ProductOptionCollectionFactory|MockObject
     */
    private $productOptionCollectionFactoryMock;

    /**
     * @var ProductAttributeCollectionFactory|MockObject
     */
    private $attributeCollectionFactoryMock;

    /**
     * @var Factory|MockObject
     */
    private $typeFactoryMock;

    /**
     * @var LinkTypeProvider|MockObject
     */
    private $linkTypeProviderMock;

    /**
     * @var Composite|MockObject
     */
    private $rowCustomizerMock;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolverMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepositoryMock;

    /**
     * @var AbstractAdapter|MockObject
     */
    private $writerMock;

    /**
     * @var AdvancedPricing|MockObject
     */
    private $advancedPricingMock;

    /**
     * @var Product
     */
    private $object;

    protected function setUp(): void
    {
        $this->localeDateMock = $this->createMock(Timezone::class);
        $this->configMock = $this->createPartialMock(Config::class, ['getEntityType']);
        $type = $this->createMock(Type::class);
        $this->configMock->expects($this->once())->method('getEntityType')->willReturn($type);
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->storeManagerMock = $this->createMock(StoreManager::class);
        $this->loggerMock = $this->createMock(Monolog::class);
        $this->collectionFactoryMock = $this->createMock(ProductCollectionFactory::class);
        $this->abstractCollectionMock = $this->getMockForAbstractClass(
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
        $this->exportConfigMock = $this->createMock(ExportConfig::class);
        $this->productFactoryMock = $this->createPartialMock(ProductFactory::class, ['create', 'getTypeId']);
        $this->attributeSetCollectionFactoryMock = $this->createPartialMock(
            AttibuteSetCollectionFactory::class,
            [
                'create',
                'setEntityTypeFilter',
            ]
        );
        $this->categoryColFactoryMock = $this->createPartialMock(
            CategoryCollectionFactory::class,
            [
                'create',
                'addNameToResult',
            ]
        );
        $this->itemFactoryMock = $this->createMock(ItemFactory::class);
        $this->productOptionCollectionFactoryMock = $this->createMock(ProductOptionCollectionFactory::class);
        $this->attributeCollectionFactoryMock = $this->createMock(ProductAttributeCollectionFactory::class);
        $this->typeFactoryMock = $this->createMock(Factory::class);
        $this->linkTypeProviderMock = $this->createMock(LinkTypeProvider::class);
        $this->rowCustomizerMock = $this->createMock(Composite::class);
        $this->storeResolverMock = $this->createMock(StoreResolver::class);
        $this->groupRepositoryMock = $this->createMock(GroupRepositoryInterface::class);
        $this->writerMock = $this->createPartialMock(
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
        $this->advancedPricingMock = $this->getMockBuilder(AdvancedPricing::class)
            ->setMethods($mockMethods)
            ->disableOriginalConstructor()
            ->getMock();
        foreach ($constructorMethods as $method) {
            $this->advancedPricingMock->expects($this->once())->method($method)->will($this->returnSelf());
        }
        $this->advancedPricingMock->__construct(
            $this->localeDateMock,
            $this->configMock,
            $this->resourceMock,
            $this->storeManagerMock,
            $this->loggerMock,
            $this->collectionFactoryMock,
            $this->exportConfigMock,
            $this->productFactoryMock,
            $this->attributeSetCollectionFactoryMock,
            $this->categoryColFactoryMock,
            $this->itemFactoryMock,
            $this->productOptionCollectionFactoryMock,
            $this->attributeCollectionFactoryMock,
            $this->typeFactoryMock,
            $this->linkTypeProviderMock,
            $this->rowCustomizerMock,
            $this->storeResolverMock,
            $this->groupRepositoryMock
        );
    }

    /**
     * Test export with zero condition
     */
    public function testExportZeroConditionCalls()
    {
        $page = 1;
        $itemsPerPage = 10;

        $this->advancedPricingMock->expects($this->once())->method('getWriter')->willReturn($this->writerMock);
        $this->advancedPricingMock
            ->expects($this->exactly(1))
            ->method('_getEntityCollection')
            ->willReturn($this->abstractCollectionMock);
        $this->advancedPricingMock
            ->expects($this->once())
            ->method('_prepareEntityCollection')
            ->with($this->abstractCollectionMock);
        $this->advancedPricingMock->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->advancedPricingMock->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollectionMock->expects($this->once())->method('setOrder')->with('has_options', 'asc');
        $this->abstractCollectionMock->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);
        $this->abstractCollectionMock->expects($this->once())->method('count')->willReturn(0);
        $this->abstractCollectionMock->expects($this->never())->method('getCurPage');
        $this->abstractCollectionMock->expects($this->never())->method('getLastPageNumber');
        $this->advancedPricingMock->expects($this->never())->method('_getHeaderColumns');
        $this->writerMock->expects($this->never())->method('setHeaderCols');
        $this->writerMock->expects($this->never())->method('writeRow');
        $this->advancedPricingMock->expects($this->never())->method('getExportData');
        $this->advancedPricingMock->expects($this->never())->method('_customFieldsMapping');
        $this->writerMock->expects($this->once())->method('getContents');
        $this->advancedPricingMock->export();
    }

    /**
     * Test export for current page
     */
    public function testExportCurrentPageCalls()
    {
        $curPage = $lastPage = $page = 1;
        $itemsPerPage = 10;
        $this->advancedPricingMock->expects($this->once())->method('getWriter')->willReturn($this->writerMock);
        $this->advancedPricingMock
            ->expects($this->exactly(1))
            ->method('_getEntityCollection')
            ->willReturn($this->abstractCollectionMock);
        $this->advancedPricingMock
            ->expects($this->once())
            ->method('_prepareEntityCollection')
            ->with($this->abstractCollectionMock);
        $this->advancedPricingMock->expects($this->once())->method('getItemsPerPage')->willReturn($itemsPerPage);
        $this->advancedPricingMock->expects($this->once())->method('paginateCollection')->with($page, $itemsPerPage);
        $this->abstractCollectionMock->expects($this->once())->method('setOrder')->with('has_options', 'asc');
        $this->abstractCollectionMock->expects($this->once())->method('setStoreId')->with(Store::DEFAULT_STORE_ID);
        $this->abstractCollectionMock->expects($this->once())->method('count')->willReturn(1);
        $this->abstractCollectionMock->expects($this->once())->method('getCurPage')->willReturn($curPage);
        $this->abstractCollectionMock->expects($this->once())->method('getLastPageNumber')->willReturn($lastPage);
        $headers = ['headers'];
        $this->advancedPricingMock->expects($this->any())->method('_getHeaderColumns')->willReturn($headers);
        $this->writerMock->expects($this->any())->method('setHeaderCols')->with($headers);
        $webSite = 'All Websites [USD]';
        $userGroup = 'General';
        $this->advancedPricingMock->expects($this->any())->method('_getWebsiteCode')->willReturn($webSite);
        $this->advancedPricingMock->expects($this->any())->method('_getCustomerGroupById')->willReturn($userGroup);
        $data = [
            [
                'sku' => 'simpletest',
                'tier_price_website' => $webSite,
                'tier_price_customer_group' => $userGroup,
                'tier_price_qty' => '2',
                'tier_price' => '23',
            ]
        ];
        $this->advancedPricingMock->expects($this->once())->method('getExportData')->willReturn($data);
        $exportData = [
            'sku' => 'simpletest',
            'tier_price_website' => $webSite,
            'tier_price_customer_group' => $userGroup,
            'tier_price_qty' => '2',
            'tier_price' => '23',
        ];
        $this->advancedPricingMock
            ->expects($this->any())
            ->method('correctExportData')
            ->willReturn($exportData);
        $this->writerMock->expects($this->once())->method('writeRow')->with($exportData);
        $this->writerMock->expects($this->once())->method('getContents');
        $this->advancedPricingMock->export();
    }

    protected function tearDown(): void
    {
        unset($this->object);
    }
}
