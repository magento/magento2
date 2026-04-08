<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\ResourceModel\Quote\Item;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Class to test quote item collection
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var ManagerInterface|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Select|MockObject
     */
    protected $selectMock;

    /**
     * @var AbstractDb|MockObject
     */
    protected $resourceMock;

    /**
     * @var FetchStrategyInterface|MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var EntityFactory|MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);
        $this->fetchStrategyMock = $this->createMock(FetchStrategyInterface::class);
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);

        $this->selectMock = $this->createMock(Select::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($this->selectMock);

        $this->resourceMock = $this->createMock(AbstractDb::class);
        $this->resourceMock->method('getConnection')->willReturn(
            $this->connectionMock
        );

        $objectManager = new ObjectManager($this);
        $this->collection = $objectManager->getObject(
            Collection::class,
            [
                'entityFactory' => $this->entityFactoryMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->eventManagerMock,
                'resource' => $this->resourceMock
            ]
        );
    }

    public function testInstanceOf()
    {
        $this->assertInstanceOf(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Collection::class,
            $this->collection
        );
    }

    public function testRemoveItemsWithAbsentProducts(): void
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getItems', 'removeItemByKey'])
            ->getMock();
        $productCollectionFactory = $this->createMock(ProductCollectionFactory::class);
        $productCollection = $this->createMock(ProductCollection::class);
        $productCollectionFactory->expects($this->once())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->once())->method('addIdFilter')->willReturnSelf();
        $productCollection->expects($this->once())->method('getAllIds')->willReturn([20]);
        $productCollectionFactoryProperty = new \ReflectionProperty(
            Collection::class,
            '_productCollectionFactory'
        );
        $productCollectionFactoryProperty->setValue($collection, $productCollectionFactory);
        $productIdsProperty = new \ReflectionProperty(Collection::class, '_productIds');
        $productIdsProperty->setValue($collection, [10, 10, 20, 30]);
        $firstAbsentProductItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $firstAbsentProductItem->setData('product_id', 10);
        $firstAbsentProductItem->method('getId')->willReturn(101);
        $secondAbsentProductItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $secondAbsentProductItem->setData('product_id', 10);
        $secondAbsentProductItem->method('getId')->willReturn(102);
        $existingProductItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $existingProductItem->setData('product_id', 20);
        $existingProductItem->method('getId')->willReturn(201);
        $thirdAbsentProductItem = $this->getMockBuilder(QuoteItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();
        $thirdAbsentProductItem->setData('product_id', 30);
        $thirdAbsentProductItem->method('getId')->willReturn(301);
        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn([
                $firstAbsentProductItem,
                $secondAbsentProductItem,
                $existingProductItem,
                $thirdAbsentProductItem,
            ]);
        $removeCallIndex = 0;
        $collection->expects($this->exactly(3))
            ->method('removeItemByKey')
            ->willReturnCallback(function (int $itemId) use (&$removeCallIndex,): void {
                $expectedRemovedIds = [101, 102, 301];
                $this->assertSame($expectedRemovedIds[$removeCallIndex], $itemId);
                $removeCallIndex++;
            });
        $method = new \ReflectionMethod(Collection::class, 'removeItemsWithAbsentProducts');
        $method->invoke($collection);
        $this->assertSame(3, $removeCallIndex);
    }
}
