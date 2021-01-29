<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Status\History;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\History\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $historyItemMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->selectMock = $this->createMock(\Magento\Framework\DB\Select::class);
        $this->historyItemMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Status\History::class,
            ['__wakeup', 'addData']
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            \Magento\Sales\Model\ResourceModel\EntityAbstract::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            \Magento\Framework\Data\Collection\Db\FetchStrategyInterface::class
        );
        $this->entityFactoryMock = $this->createMock(\Magento\Framework\Data\Collection\EntityFactory::class);

        $this->resourceMock->expects($this->any())->method('getConnection')->willReturn(
            $this->connectionMock
        );
        $this->resourceMock->expects($this->any())->method('getTable')->willReturnArgument(0);

        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->willReturnArgument(0);
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->willReturn($this->selectMock);

        $data = [['data']];
        $this->historyItemMock->expects($this->once())
            ->method('addData')
            ->with($this->equalTo($data[0]))
            ->willReturn($this->historyItemMock);

        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($data);

        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->historyItemMock);

        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->collection = new \Magento\Sales\Model\ResourceModel\Order\Status\History\Collection(
            $this->entityFactoryMock,
            $logger,
            $this->fetchStrategyMock,
            $this->eventManagerMock,
            $this->entitySnapshotMock,
            $this->connectionMock,
            $this->resourceMock
        );
    }

    public function testGetUnnotifiedForInstance()
    {
        $orderId = 100000512;
        $entityType = 'order';

        $order = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['__wakeup',
            'getEntityType',
            'getId']);
        $order->expects($this->once())
            ->method('getEntityType')
            ->willReturn($entityType);
        $order->expects($this->once())
            ->method('getId')
            ->willReturn($orderId);

        $this->connectionMock = $this->collection->getResource()->getConnection();
        $this->connectionMock->expects($this->exactly(3))
            ->method('prepareSqlCondition')
            ->willReturnMap(
                
                    [
                        ['entity_name', $entityType, 'sql-string'],
                        ['is_customer_notified', 0, 'sql-string'],
                        ['parent_id', $orderId, 'sql-string'],
                    ]
                
            );
        $result = $this->collection->getUnnotifiedForInstance($order);
        $this->assertEquals($this->historyItemMock, $result);
    }
}
