<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\Indexer\Product\Flat\State;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Backend\Tierprice;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\Catalog\Model\ResourceModel\Helper;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\UniversalFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var MockObject
     */
    protected $selectMock;

    /**
     * @var MockObject
     */
    protected $connectionMock;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var MockObject
     */
    private $galleryResourceMock;

    /**
     * @var MockObject
     */
    private $entityMock;

    /**
     * @var MockObject
     */
    private $metadataPoolMock;

    /**
     * @var MockObject
     */
    private $galleryReadHandlerMock;

    /**
     * @var MockObject
     */
    private $storeManager;

    /**
     * @var ProductLimitation|MockObject
     */
    private $productLimitationMock;

    /**
     * @var EntityFactory|MockObject
     */
    private $entityFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->entityFactory = $this->createMock(EntityFactory::class);
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fetchStrategy = $this->getMockBuilder(FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eventManager = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eavConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityFactory = $this->getMockBuilder(\Magento\Eav\Model\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceHelper = $this->getMockBuilder(Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory = $this->getMockBuilder(UniversalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId', 'getWebsiteId'])
            ->onlyMethods(['getStore'])
            ->getMockForAbstractClass();
        $moduleManager = $this->getMockBuilder(Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductFlatState = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productOptionFactory = $this->getMockBuilder(OptionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogUrl = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeDate = $this->getMockBuilder(TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupManagement = $this->getMockBuilder(GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->connectionMock = $this->getMockBuilder(AdapterInterface::class)
            ->addMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityMock = $this->getMockBuilder(AbstractEntity::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->galleryResourceMock = $this->getMockBuilder(
            Gallery::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->metadataPoolMock = $this->getMockBuilder(
            MetadataPool::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->galleryReadHandlerMock = $this->getMockBuilder(
            ReadHandler::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStore')->willReturnSelf();
        $universalFactory->expects($this->exactly(1))->method('create')->willReturnOnConsecutiveCalls(
            $this->entityMock
        );
        $this->entityMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn([]);
        $this->entityMock->expects($this->any())->method('getTable')->willReturnArgument(0);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);

        $this->productLimitationMock = $this->createMock(
            ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMockBuilder(
            ProductLimitationFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])->getMock();

        $productLimitationFactoryMock->method('create')
            ->willReturn($this->productLimitationMock);
        $this->collection = $this->objectManager->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactory,
                'logger' => $logger,
                'fetchStrategy' => $fetchStrategy,
                'eventManager' => $eventManager,
                'eavConfig' => $eavConfig,
                'resource' => $resource,
                'eavEntityFactory' => $eavEntityFactory,
                'resourceHelper' => $resourceHelper,
                'universalFactory' => $universalFactory,
                'storeManager' => $this->storeManager,
                'moduleManager' => $moduleManager,
                'catalogProductFlatState' => $catalogProductFlatState,
                'scopeConfig' => $scopeConfig,
                'productOptionFactory' => $productOptionFactory,
                'catalogUrl' => $catalogUrl,
                'localeDate' => $localeDate,
                'customerSession' => $customerSession,
                'dateTime' => $dateTime,
                'groupManagement' => $groupManagement,
                'connection' => $this->connectionMock,
                'productLimitationFactory' => $productLimitationFactoryMock,
                'metadataPool' => $this->metadataPoolMock,
                '_isCollectionLoaded' => true
            ]
        );
        $this->collection->setConnection($this->connectionMock);
        $this->objectManager->setBackwardCompatibleProperty(
            $this->collection,
            'mediaGalleryResource',
            $this->galleryResourceMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->collection,
            'productGalleryReadHandler',
            $this->galleryReadHandlerMock
        );
    }

    public function testAddProductCategoriesFilter()
    {
        $condition = ['in' => [1, 2]];
        $values = [1, 2];
        $conditionType = 'nin';
        $preparedSql = "category_id IN(1,2)";
        $tableName = "catalog_category_product";
        $this->connectionMock->expects($this->any())->method('getId')->willReturn(1);
        $this->connectionMock->expects($this->exactly(2))->method('prepareSqlCondition')
            ->willReturnCallback(function ($arg1, $arg2) use ($preparedSql, $conditionType, $condition) {
                if ($arg1 == 'cat.category_id' && $arg2 == $condition) {
                    return $preparedSql;
                } elseif ($arg1 == 'e.entity_id' && $arg2 == [$conditionType => $this->selectMock]) {
                    return 'e.entity_id IN (1,2)';
                }
            });
        $this->selectMock->expects($this->once())->method('from')->with(
            ['cat' => $tableName],
            'cat.product_id'
        )->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))->method('where')
            ->willReturnCallback(function ($arg) use ($preparedSql) {
                if ($arg == $preparedSql) {
                    return $this->selectMock;
                } elseif ($arg == 'e.entity_id IN (1,2)') {
                    return $this->selectMock;
                }
            });
        $this->collection->addCategoriesFilter([$conditionType => $values]);
    }

    public function testAddMediaGalleryData()
    {
        $attributeId = 42;
        $rowId = 4;
        $linkField = 'row_id';
        $mediaGalleriesMock = [[$linkField => $rowId]];
        $itemMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getOrigData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataMock = $this->getMockBuilder(EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->collection->addItem($itemMock);
        $this->galleryResourceMock->expects($this->once())->method('createBatchBaseSelect')->willReturn($selectMock);
        $attributeMock->expects($this->once())->method('getAttributeId')->willReturn($attributeId);
        $this->entityMock->expects($this->once())->method('getAttribute')->willReturn($attributeMock);
        $itemMock->expects($this->atLeastOnce())->method('getOrigData')->willReturn($rowId);
        $selectMock->expects($this->once())->method('reset')->with(Select::ORDER)->willReturnSelf();
        $selectMock->expects($this->once())->method('where')->with('entity.' . $linkField . ' IN (?)', [$rowId])
            ->willReturnSelf();
        $this->metadataPoolMock->expects($this->once())->method('getMetadata')->willReturn($metadataMock);
        $metadataMock->expects($this->once())->method('getLinkField')->willReturn($linkField);

        $this->connectionMock->expects($this->once())->method('fetchOne')->with($selectMock)->willReturn(42);
        $this->connectionMock->expects($this->once())->method('fetchAll')->with($selectMock)->willReturn(
            [['row_id' => $rowId]]
        );
        $this->galleryReadHandlerMock->expects($this->once())->method('addMediaDataToProduct')
            ->with($itemMock, $mediaGalleriesMock);

        $this->assertSame($this->collection, $this->collection->addMediaGalleryData());
    }

    /**
     * Test addTierPriceDataByGroupId method.
     *
     * @return void
     */
    public function testAddTierPriceDataByGroupId()
    {
        $customerGroupId = 2;
        $itemMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['isScopeGlobal'])
            ->onlyMethods(['getBackend'])
            ->getMock();
        $backend = $this->getMockBuilder(Tierprice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(
            AbstractGroupPrice::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->once())->method('getAutoIncrementField')->willReturn('entity_id');
        $this->collection->addItem($itemMock);
        $itemMock->expects($this->atLeastOnce())->method('getData')->with('entity_id')->willReturn(1);
        $this->entityMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->with('tier_price')
            ->willReturn($attributeMock);
        $attributeMock->expects($this->atLeastOnce())->method('getBackend')->willReturn($backend);
        $attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(false);
        $this->storeManager->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $backend->expects($this->once())->method('getResource')->willReturn($resource);
        $resource->expects($this->once())->method('getSelect')->willReturn($select);
        $select->expects($this->once())->method('columns')->with(['product_id' => 'entity_id'])->willReturnSelf();
        $select->expects($this->exactly(2))->method('where')
            ->willReturnCallback(function ($arg1, $arg2) use ($customerGroupId, $select) {
                if ($arg1 == 'entity_id IN(?)' && $arg2 == [1]) {
                    return $select;
                } elseif ($arg1 == '(customer_group_id=? AND all_groups=0) OR all_groups=1' &&
                    $arg2 == $customerGroupId) {
                    return $select;
                }
            });
        $select->expects($this->once())->method('order')->with('qty')->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([['product_id' => 1]]);
        $backend->expects($this->once())->method('setPriceData')->with($itemMock, [['product_id' => 1]]);

        $this->assertSame($this->collection, $this->collection->addTierPriceDataByGroupId($customerGroupId));
    }

    /**
     * Test testAddTierPriceData method.
     *
     * @return void
     */
    public function testAddTierPriceData()
    {
        $itemMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->addMethods(['isScopeGlobal'])
            ->onlyMethods(['getBackend'])
            ->getMock();
        $backend = $this->getMockBuilder(Tierprice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(
            AbstractGroupPrice::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->connectionMock->expects($this->once())->method('getAutoIncrementField')->willReturn('entity_id');
        $this->collection->addItem($itemMock);
        $itemMock->expects($this->atLeastOnce())->method('getData')->with('entity_id')->willReturn(1);
        $this->entityMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->with('tier_price')
            ->willReturn($attributeMock);
        $attributeMock->expects($this->atLeastOnce())->method('getBackend')->willReturn($backend);
        $attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(false);
        $this->storeManager->expects($this->once())->method('getWebsiteId')->willReturn(1);
        $backend->expects($this->once())->method('getResource')->willReturn($resource);
        $resource->expects($this->once())->method('getSelect')->willReturn($select);
        $select->expects($this->once())->method('columns')->with(['product_id' => 'entity_id'])->willReturnSelf();
        $select->expects($this->exactly(1))->method('where')
            ->with('entity_id IN(?)', [1])
            ->willReturnSelf();
        $select->expects($this->once())->method('order')->with('qty')->willReturnSelf();
        $this->connectionMock->expects($this->once())
            ->method('fetchAll')
            ->with($select)
            ->willReturn([['product_id' => 1]]);
        $backend->expects($this->once())->method('setPriceData')->with($itemMock, [['product_id' => 1]]);

        $this->assertSame($this->collection, $this->collection->addTierPriceData());
    }

    /**
     * Test for getNewEmptyItem() method
     *
     * @return void
     */
    public function testGetNewEmptyItem()
    {
        $item = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityFactory->expects($this->once())->method('create')->willReturn($item);
        $firstItem = $this->collection->getNewEmptyItem();
        $secondItem = $this->collection->getNewEmptyItem();
        $this->assertEquals($firstItem, $secondItem);
    }

    /**
     * Test to add website filter in admin area
     */
    public function testAddWebsiteFilterOnAdminStore(): void
    {
        $websiteIds = [2];
        $websiteTable = 'catalog_product_website';
        $joinCondition = 'join condition';
        $this->productLimitationMock->expects($this->atLeastOnce())
            ->method('offsetSet')
            ->with('website_ids', $websiteIds);
        $this->productLimitationMock->method('offsetExists')
            ->with('website_ids')
            ->willReturn(true);
        $this->productLimitationMock->method('offsetGet')
            ->with('website_ids')
            ->willReturn($websiteIds);
        $this->connectionMock->expects($this->once())
            ->method('quoteInto')
            ->with('product_website.website_id IN(?)', $websiteIds, 'int')
            ->willReturn($joinCondition);
        $this->selectMock->method('getPart')->with(Select::FROM)->willReturn([]);
        /** @var AbstractEntity|MockObject $eavEntity */
        $eavEntity = $this->createMock(AbstractEntity::class);
        $eavEntity->method('getTable')
            ->with('catalog_product_website')
            ->willReturn($websiteTable);
        $this->selectMock->expects($this->once())
            ->method('join')
            ->with(
                ['product_website' => $websiteTable],
                'product_website.product_id = e.entity_id AND ' . $joinCondition,
                []
            );

        $this->collection->setEntity($eavEntity);
        $this->collection->setStoreId(Store::DEFAULT_STORE_ID);
        $this->collection->addWebsiteFilter($websiteIds);
    }
}
