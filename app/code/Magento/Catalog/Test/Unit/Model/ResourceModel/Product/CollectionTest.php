<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel\Product;

use Magento\Catalog\Model\Indexer;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection\ProductLimitationFactory;
use Magento\Framework\DB\Select;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB;
use Magento\Framework\EntityManager\EntityMetadataInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event;
use Magento\Framework\Model\ResourceModel\ResourceModelPoolInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
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
     * @var \PHPUnit_Framework_MockObject_MockObject|DB\Adapter\AdapterInterface
     */
    protected $connectionMock;

    /**
     * @var ProductResource\Collection
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
        $this->selectMock = $this->createMock(DB\Select::class);
        $this->connectionMock = $this->createMock(DB\Adapter\AdapterInterface::class);
        $this->connectionMock->expects($this->atLeastOnce())->method('select')->willReturn($this->selectMock);
        $this->entityMock = $this->createMock(AbstractEntity::class);
        $this->entityMock->expects($this->once())->method('getConnection')->willReturn($this->connectionMock);
        $this->entityMock->expects($this->once())->method('getDefaultAttributes')->willReturn([]);
        $this->entityMock->method('getTable')->willReturnArgument(0);
        $this->galleryResourceMock = $this->createMock(ProductResource\Gallery::class);
        $this->metadataPoolMock = $this->createMock(MetadataPool::class);
        $this->galleryReadHandlerMock = $this->createMock(ProductModel\Gallery\ReadHandler::class);

        $storeStub = $this->createMock(StoreInterface::class);
        $storeStub->method('getId')->willReturn(1);
        $storeStub->method('getWebsiteId')->willReturn(1);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->storeManager->method('getStore')->willReturn($storeStub);
        $resourceModelPool = $this->createMock(ResourceModelPoolInterface::class);
        $resourceModelPool->expects($this->exactly(1))->method('get')->willReturn($this->entityMock);

        $productLimitationFactoryMock = $this->createPartialMock(ProductLimitationFactory::class, ['create']);
        $productLimitationFactoryMock->method('create')
            ->willReturn($this->createMock(ProductResource\Collection\ProductLimitation::class));
        $this->collection = $this->objectManager->getObject(
            ProductResource\Collection::class,
            [
                'entityFactory' => $this->entityFactory,
                'logger' => $this->createMock(LoggerInterface::class),
                'fetchStrategy' => $this->createMock(FetchStrategyInterface::class),
                'eventManager' => $this->createMock(Event\ManagerInterface::class),
                'eavConfig' => $this->createMock(\Magento\Eav\Model\Config::class),
                'resource' => $this->createMock(ResourceConnection::class),
                'eavEntityFactory' => $this->createMock(EntityFactory::class),
                'resourceHelper' => $this->createMock(\Magento\Catalog\Model\ResourceModel\Helper::class),
                'resourceModelPool' => $resourceModelPool,
                'storeManager' => $this->storeManager,
                'moduleManager' => $this->createMock(\Magento\Framework\Module\Manager::class),
                'catalogProductFlatState' => $this->createMock(Indexer\Product\Flat\State::class),
                'scopeConfig' => $this->createMock(ScopeConfigInterface::class),
                'productOptionFactory' => $this->createMock(ProductModel\OptionFactory::class),
                'catalogUrl' => $this->createMock(\Magento\Catalog\Model\ResourceModel\Url::class),
                'localeDate' => $this->createMock(TimezoneInterface::class),
                'customerSession' => $this->createMock(\Magento\Customer\Model\Session::class),
                'dateTime' => $this->createMock(\Magento\Framework\Stdlib\DateTime::class),
                'groupManagement' => $this->createMock(\Magento\Customer\Api\GroupManagementInterface::class),
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
        $preparedSql = 'category_id IN(1,2)';
        $tableName = 'catalog_category_product';
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
        /** @var ProductModel|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockBuilder(ProductModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrigData'])
            ->getMock();
        $attributeMock = $this->createMock(AbstractAttribute::class);
        $selectMock = $this->createMock(DB\Select::class);
        $metadataMock = $this->createMock(EntityMetadataInterface::class);
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
        /** @var ProductModel|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->createMock(ProductModel::class);
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isScopeGlobal', 'getBackend'])
            ->getMock();
        $backend = $this->createMock(ProductModel\Attribute\Backend\Tierprice::class);
        $resource = $this->createMock(ProductResource\Attribute\Backend\GroupPrice\AbstractGroupPrice::class);
        $select = $this->createMock(DB\Select::class);
        $this->connectionMock->expects($this->once())->method('getAutoIncrementField')->willReturn('entity_id');
        $this->collection->addItem($itemMock);
        $itemMock->expects($this->atLeastOnce())->method('getData')->with('entity_id')->willReturn(1);
        $this->entityMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->with('tier_price')
            ->willReturn($attributeMock);
        $attributeMock->expects($this->atLeastOnce())->method('getBackend')->willReturn($backend);
        $attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(false);
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
        /** @var ProductModel|\PHPUnit_Framework_MockObject_MockObject $itemMock */
        $itemMock = $this->getMockBuilder(ProductModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData'])
            ->getMock();
        $attributeMock = $this->getMockBuilder(AbstractAttribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['isScopeGlobal', 'getBackend'])
            ->getMock();
        $backend = $this->createMock(ProductModel\Attribute\Backend\Tierprice::class);
        $resource = $this->getMockBuilder(
            ProductResource\Attribute\Backend\GroupPrice\AbstractGroupPrice::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $select = $this->createMock(DB\Select::class);
        $this->connectionMock->expects($this->once())->method('getAutoIncrementField')->willReturn('entity_id');
        $this->collection->addItem($itemMock);
        $itemMock->expects($this->atLeastOnce())->method('getData')->with('entity_id')->willReturn(1);
        $this->entityMock->expects($this->atLeastOnce())
            ->method('getAttribute')
            ->with('tier_price')
            ->willReturn($attributeMock);
        $attributeMock->expects($this->atLeastOnce())->method('getBackend')->willReturn($backend);
        $attributeMock->expects($this->once())->method('isScopeGlobal')->willReturn(false);
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
