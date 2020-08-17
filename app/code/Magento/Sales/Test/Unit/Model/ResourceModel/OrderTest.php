<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\SalesSequence\Model\Manager;
use Magento\SalesSequence\Model\Sequence;
use Magento\Store\Model\Group;
use Magento\Store\Model\Store;
use Magento\Store\Model\Website;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

/**
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    protected $resource;

    /**
     * @var ResourceConnection|MockObject
     */
    protected $resourceMock;

    /**
     * @var Manager|MockObject
     */
    protected $salesSequenceManagerMock;

    /**
     * @var Sequence|MockObject
     */
    protected $salesSequenceMock;

    /**
     * @var \Magento\Sales\Model\Order|MockObject
     */
    protected $orderMock;

    /**
     * @var Item|MockObject
     */
    protected $orderItemMock;

    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    /**
     * @var Website|MockObject
     */
    protected $websiteMock;

    /**
     * @var Group|MockObject
     */
    protected $storeGroupMock;

    /**
     * @var AdapterInterface|MockObject
     */
    protected $connectionMock;

    /**
     * @var Snapshot|MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|MockObject
     */
    protected $relationCompositeMock;

    /**
     * @var ObjectRelationProcessor|MockObject
     */
    protected $objectRelationProcessorMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(ResourceConnection::class);
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderItemMock = $this->getMockBuilder(Item::class)
            ->addMethods(['getQuoteParentItemId', 'setTotalItemCount'])
            ->onlyMethods(['getChildrenItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->createMock(Store::class);
        $this->storeGroupMock = $this->createPartialMock(
            Group::class,
            ['getName', 'getDefaultStoreId']
        );
        $this->websiteMock = $this->createPartialMock(Website::class, ['getName']);
        $this->connectionMock = $this->getMockBuilder(Mysql::class)
            ->onlyMethods(
                [
                    'rollback',
                    'describeTable',
                    'insert',
                    'lastInsertId',
                    'beginTransaction',
                    'commit',
                    'quoteInto',
                    'update'
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
        $this->salesSequenceManagerMock = $this->createMock(Manager::class);
        $this->salesSequenceMock = $this->createMock(Sequence::class);
        $this->entitySnapshotMock = $this->createMock(
            Snapshot::class
        );
        $this->relationCompositeMock = $this->createMock(
            RelationComposite::class
        );
        $this->objectRelationProcessorMock = $this->createMock(
            ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->objectRelationProcessorMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->resource = $objectManager->getObject(
            Order::class,
            [
                'context' => $contextMock,
                'sequenceManager' => $this->salesSequenceManagerMock,
                'entitySnapshot' => $this->entitySnapshotMock,
                'entityRelationComposite' => $this->relationCompositeMock
            ]
        );
    }

    public function testSave()
    {
        $this->orderMock->expects($this->exactly(3))
            ->method('getId')
            ->willReturn(null);
        $this->orderItemMock->expects($this->once())
            ->method('getChildrenItems')
            ->willReturn([]);
        $this->orderItemMock->expects($this->once())
            ->method('getQuoteParentItemId')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('setTotalItemCount')
            ->with(1);
        $this->storeGroupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn(1);
        $this->orderMock->expects($this->once())
            ->method('getAllItems')
            ->willReturn([$this->orderItemMock]);
        $this->orderMock->expects($this->once())
            ->method('validateBeforeSave')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('beforeSave')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('isSaveAllowed')
            ->willReturn(true);
        $this->orderMock->expects($this->once())
            ->method('getEntityType')
            ->willReturn('order');
        $this->orderMock->expects($this->exactly(2))
            ->method('getStore')
            ->willReturn($this->storeMock);
        $this->storeMock->expects($this->exactly(2))
            ->method('getGroup')
            ->willReturn($this->storeGroupMock);
        $this->storeMock->expects($this->once())
            ->method('getWebsite')
            ->willReturn($this->websiteMock);
        $this->storeGroupMock->expects($this->once())
            ->method('getDefaultStoreId')
            ->willReturn(1);
        $this->salesSequenceManagerMock->expects($this->once())
            ->method('getSequence')
            ->with('order', 1)
            ->willReturn($this->salesSequenceMock);
        $this->salesSequenceMock->expects($this->once())
            ->method('getNextValue')
            ->willReturn('10000001');
        $this->orderMock->expects($this->once())
            ->method('setIncrementId')
            ->with('10000001')
            ->willReturnSelf();
        $this->orderMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(null);
        $this->orderMock->expects($this->once())
            ->method('getData')
            ->willReturn(['increment_id' => '10000001']);
        $this->objectRelationProcessorMock->expects($this->once())
            ->method('validateDataIntegrity')
            ->with(null, ['increment_id' => '10000001']);
        $this->relationCompositeMock->expects($this->once())
            ->method('processRelations')
            ->with($this->orderMock);
        $this->resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->connectionMock);
        $this->connectionMock->expects($this->any())
            ->method('quoteInto');
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->willReturn([]);
        $this->connectionMock->expects($this->any())
            ->method('update');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->willReturn(1);
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->orderMock)
            ->willReturn(true);
        $this->resource->save($this->orderMock);
    }
}
