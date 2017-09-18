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
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\DB\Select|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectMock;

    /**
     * @var \Magento\Sales\Model\Order\Status\History|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $historyItemMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $fetchStrategyMock;

    /**
     * @var \Magento\Framework\Data\Collection\EntityFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityFactoryMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entitySnapshotMock;

    protected function setUp()
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

        $this->resourceMock->expects($this->any())->method('getConnection')->will(
            $this->returnValue($this->connectionMock)
        );
        $this->resourceMock->expects($this->any())->method('getTable')->will($this->returnArgument(0));

        $this->connectionMock->expects($this->any())->method('quoteIdentifier')->will($this->returnArgument(0));
        $this->connectionMock->expects($this->atLeastOnce())
            ->method('select')
            ->will($this->returnValue($this->selectMock));

        $data = [['data']];
        $this->historyItemMock->expects($this->once())
            ->method('addData')
            ->with($this->equalTo($data[0]))
            ->will($this->returnValue($this->historyItemMock));

        $this->fetchStrategyMock->expects($this->once())
            ->method('fetchAll')
            ->will($this->returnValue($data));

        $this->entityFactoryMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($this->historyItemMock));

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
            ->will($this->returnValue($entityType));
        $order->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($orderId));

        $this->connectionMock = $this->collection->getResource()->getConnection();
        $this->connectionMock->expects($this->exactly(3))
            ->method('prepareSqlCondition')
            ->will(
                $this->returnValueMap(
                    [
                        ['entity_name', $entityType, 'sql-string'],
                        ['is_customer_notified', 0, 'sql-string'],
                        ['parent_id', $orderId, 'sql-string'],
                    ]
                )
            );
        $result = $this->collection->getUnnotifiedForInstance($order);
        $this->assertEquals($this->historyItemMock, $result);
    }
}
