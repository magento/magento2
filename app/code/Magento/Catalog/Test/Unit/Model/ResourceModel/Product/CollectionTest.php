<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Framework\DB\Select;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $galleryResourceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $entityMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadataPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $galleryReadHandlerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $entityFactory;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->entityFactory = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);
        $logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fetchStrategy = $this->getMockBuilder(\Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eventManager = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $eavConfig = $this->getMockBuilder(\Magento\Eav\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(\Magento\Framework\App\ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eavEntityFactory = $this->getMockBuilder(\Magento\Eav\Model\EntityFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resourceHelper = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Helper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $universalFactory = $this->getMockBuilder(\Magento\Framework\Validator\UniversalFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'getId', 'getWebsiteId'])
            ->getMockForAbstractClass();
        $moduleManager = $this->getMockBuilder(\Magento\Framework\Module\Manager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogProductFlatState = $this->getMockBuilder(\Magento\Catalog\Model\Indexer\Product\Flat\State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productOptionFactory = $this->getMockBuilder(\Magento\Catalog\Model\Product\OptionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $catalogUrl = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Url::class)
            ->disableOriginalConstructor()
            ->getMock();
        $localeDate = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerSession = $this->getMockBuilder(\Magento\Customer\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $dateTime = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupManagement = $this->getMockBuilder(\Magento\Customer\Api\GroupManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->connectionMock = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\AbstractEntity::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->galleryResourceMock = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Gallery::class
        )->disableOriginalConstructor()->getMock();

        $this->metadataPoolMock = $this->getMockBuilder(
            \Magento\Framework\EntityManager\MetadataPool::class
        )->disableOriginalConstructor()->getMock();

        $this->galleryReadHandlerMock = $this->getMockBuilder(
            \Magento\Catalog\Model\Product\Gallery\ReadHandler::class
        )->disableOriginalConstructor()->getMock();

        $this->storeManager->expects($this->any())->method('getId')->willReturn(1);
        $this->storeManager->expects($this->any())->method('getStore')->willReturnSelf();
        $universalFactory->expects($this->exactly(1))->method('create')->willReturnOnConsecutiveCalls(
            $this->entityMock
        );
        $this->entityMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn([]);
        $this->entityMock->expects($this->any())->method('getTable')->willReturnArgument(0);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);

        $productLimitationMock = $this->createMock(
            \Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitation::class
        );
        $productLimitationFactoryMock = $this->getMockBuilder(
            ProductLimitationFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();

        $productLimitationFactoryMock->method('create')
            ->willReturn($productLimitationMock);
        $this->collection = $this->objectManager->getObject(
            \Magento\Catalog\Model\ResourceModel\Product\Collection::class,
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
        $this->connectionMock->expects($this->exactly(2))->method('prepareSqlCondition')->withConsecutive(
            ['cat.category_id', $condition],
            ['e.entity_id', [$conditionType => $this->selectMock]]
        )->willReturnOnConsecutiveCalls(
            $preparedSql,
            'e.entity_id IN (1,2)'
        );
        $this->selectMock->expects($this->once())->method('from')->with(
            ['cat' => $tableName],
            'cat.product_id'
        )->willReturnSelf();
        $this->selectMock->expects($this->exactly(2))->method('where')->withConsecutive(
            [$preparedSql],
            ['e.entity_id IN (1,2)']
        )->willReturnSelf();
        $this->collection->addCategoriesFilter([$conditionType => $values]);
    }

    public function testAddMediaGalleryData()
    {
        $attributeId = 42;
        $rowId = 4;
        $linkField = 'row_id';
        $mediaGalleriesMock = [[$linkField => $rowId]];
        $itemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrigData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $selectMock = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metadataMock = $this->getMockBuilder(\Magento\Framework\EntityManager\EntityMetadataInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
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
        $itemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isScopeGlobal', 'getBackend'])
            ->getMock();
        $backend = $this->getMockBuilder(\Magento\Catalog\Model\Product\Attribute\Backend\Tierprice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
            ->withConsecutive(
                ['entity_id IN(?)', [1]],
                [ '(customer_group_id=? AND all_groups=0) OR all_groups=1', $customerGroupId]
            )
            ->willReturnSelf();
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
        $itemMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isScopeGlobal', 'getBackend'])
            ->getMock();
        $backend = $this->getMockBuilder(\Magento\Catalog\Model\Product\Attribute\Backend\Tierprice::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resource = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
        $item = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityFactory->expects($this->once())->method('create')->willReturn($item);
        $firstItem = $this->collection->getNewEmptyItem();
        $secondItem = $this->collection->getNewEmptyItem();
        $this->assertEquals($firstItem, $secondItem);
    }
}
