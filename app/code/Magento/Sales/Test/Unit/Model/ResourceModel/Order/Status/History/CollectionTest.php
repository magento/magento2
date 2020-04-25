<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Status\History;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Adapter\Pdo\Mysql;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Status\History;
use Magento\Sales\Model\ResourceModel\EntityAbstract;
use Magento\Sales\Model\ResourceModel\Order\Status\History\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    protected $collection;

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
     * @var History|MockObject
     */
    protected $historyItemMock;

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

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->createMock(ManagerInterface::class);
        $this->connectionMock = $this->createMock(Mysql::class);
        $this->selectMock = $this->createMock(Select::class);
        $this->historyItemMock = $this->createPartialMock(
            History::class,
            ['__wakeup', 'addData']
        );
        $this->resourceMock = $this->getMockForAbstractClass(
            EntityAbstract::class,
            [],
            '',
            false,
            true,
            true,
            ['getConnection', 'getMainTable', 'getTable', '__wakeup']
        );
        $this->entitySnapshotMock = $this->createMock(
            Snapshot::class
        );
        $this->fetchStrategyMock = $this->getMockForAbstractClass(
            FetchStrategyInterface::class
        );
        $this->entityFactoryMock = $this->createMock(EntityFactory::class);

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

        $logger = $this->createMock(LoggerInterface::class);
        $this->collection = new Collection(
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

        $order = $this->createPartialMock(Order::class, ['__wakeup',
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
