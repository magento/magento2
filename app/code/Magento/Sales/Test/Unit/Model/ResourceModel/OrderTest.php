<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

use Magento\Sales\Model\ResourceModel\Order;

/**
 * Class OrderTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\SalesSequence\Model\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesSequenceManagerMock;

    /**
     * @var \Magento\SalesSequence\Model\Sequence|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $salesSequenceMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderItemMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $websiteMock;

    /**
     * @var \Magento\Store\Model\Group|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeGroupMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $relationCompositeMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $objectRelationProcessorMock;

    /**
     * Mock class dependencies
     */
    protected function setUp(): void
    {
        $this->resourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->orderMock = $this->createMock(\Magento\Sales\Model\Order::class);
        $this->orderItemMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getQuoteParentItemId', 'setTotalItemCount', 'getChildrenItems']
        );
        $this->storeMock = $this->createMock(\Magento\Store\Model\Store::class);
        $this->storeGroupMock = $this->createPartialMock(
            \Magento\Store\Model\Group::class,
            ['getName', 'getDefaultStoreId']
        );
        $this->websiteMock = $this->createPartialMock(\Magento\Store\Model\Website::class, ['getName']);
        $this->connectionMock = $this->createPartialMock(
            \Magento\Framework\DB\Adapter\Pdo\Mysql::class,
            [
                'describeTable',
                'insert',
                'lastInsertId',
                'beginTransaction',
                'rollback',
                'commit',
                'quoteInto',
                'update'
            ]
        );
        $this->salesSequenceManagerMock = $this->createMock(\Magento\SalesSequence\Model\Manager::class);
        $this->salesSequenceMock = $this->createMock(\Magento\SalesSequence\Model\Sequence::class);
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->relationCompositeMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class
        );
        $this->objectRelationProcessorMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );
        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->resourceMock);
        $contextMock->expects($this->once())
            ->method('getObjectRelationProcessor')
            ->willReturn($this->objectRelationProcessorMock);

        $objectManager = new ObjectManagerHelper($this);
        $this->resource = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order::class,
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
