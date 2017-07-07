<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite;
use \Magento\Sales\Model\ResourceModel\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * Class OrderTest
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\SalesSequence\Model\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesSequenceManagerMock;

    /**
     * @var \Magento\SalesSequence\Model\Sequence|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $salesSequenceMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;

    /**
     * @var \Magento\Store\Model\Website|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteMock;

    /**
     * @var \Magento\Store\Model\Group|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeGroupMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    /**
     * @var RelationComposite|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $relationCompositeMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectRelationProcessorMock;

    /**
     * Mock class dependencies
     */
    protected function setUp()
    {
        $this->resourceMock = $this->getMock(\Magento\Framework\App\ResourceConnection::class, [], [], '', false);
        $this->orderMock = $this->getMock(\Magento\Sales\Model\Order::class, [], [], '', false);
        $this->orderItemMock = $this->getMock(
            \Magento\Sales\Model\Order\Item::class,
            ['getQuoteParentItemId', 'setTotalItemCount', 'getChildrenItems'],
            [],
            '',
            false
        );
        $this->storeMock = $this->getMock(\Magento\Store\Model\Store::class, [], [], '', false);
        $this->storeGroupMock = $this->getMock(
            \Magento\Store\Model\Group::class,
            ['getName', 'getDefaultStoreId'],
            [],
            '',
            false
        );
        $this->websiteMock = $this->getMock(
            \Magento\Store\Model\Website::class,
            ['getName'],
            [],
            '',
            false
        );
        $this->connectionMock = $this->getMock(
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
            ],
            [],
            '',
            false
        );
        $this->salesSequenceManagerMock = $this->getMock(
            \Magento\SalesSequence\Model\Manager::class,
            [],
            [],
            '',
            false
        );
        $this->salesSequenceMock = $this->getMock(\Magento\SalesSequence\Model\Sequence::class, [], [], '', false);
        $this->entitySnapshotMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class,
            [],
            [],
            '',
            false
        );
        $this->relationCompositeMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite::class,
            [],
            [],
            '',
            false
        );
        $this->objectRelationProcessorMock = $this->getMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class,
            [],
            [],
            '',
            false
        );
        $contextMock = $this->getMock(\Magento\Framework\Model\ResourceModel\Db\Context::class, [], [], '', false);
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
            ->method('getIncrementId')
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
            ->will($this->returnValue([]));
        $this->connectionMock->expects($this->any())
            ->method('update');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->orderMock)
            ->will($this->returnValue(true));
        $this->resource->save($this->orderMock);
    }
}
