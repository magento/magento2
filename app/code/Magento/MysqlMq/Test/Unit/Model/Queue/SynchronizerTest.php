<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Model\Queue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface;
use Magento\MysqlMq\Model\Queue\Synchronizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SynchronizerTest extends TestCase
{
    private const QUEUE_TABLE = 'prefix_queue';

    /**
     * @var ConfigInterface|MockObject
     */
    private $topologyConfig;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @var Synchronizer
     */
    private $model;

    protected function setUp(): void
    {
        $this->topologyConfig = $this->createMock(ConfigInterface::class);
        $this->connection = $this->createMock(AdapterInterface::class);
        $resourceConnection = $this->createStub(ResourceConnection::class);
        $resourceConnection->method('getConnection')->willReturn($this->connection);
        $resourceConnection->method('getTableName')->willReturn(self::QUEUE_TABLE);

        $this->connection->expects($this->once())->method('startSetup')->willReturnSelf();
        $this->connection->expects($this->once())->method('endSetup')->willReturnSelf();

        $this->model = new Synchronizer($this->topologyConfig, $resourceConnection);
    }

    public function testSynchronizeInsertsMissingQueuesOnly()
    {
        $this->expectQueues(['queue_one', 'queue_two', 'queue_three', 'queue_two']);
        $this->expectSelectReturning(['queue_one']);

        $this->connection->expects($this->once())
            ->method('insertArray')
            ->with(self::QUEUE_TABLE, ['name'], ['queue_two', 'queue_three']);

        $this->assertSame(
            ['Created queue "queue_two".', 'Created queue "queue_three".'],
            $this->model->synchronize()
        );
    }

    public function testSynchronizeIsNoOpWhenInSync()
    {
        $this->expectQueues(['queue_one']);
        $this->expectSelectReturning(['queue_one']);

        $this->connection->expects($this->never())->method('insertArray');

        $this->assertSame([], $this->model->synchronize());
    }

    /**
     * @param string[] $names
     * @return void
     */
    private function expectQueues(array $names): void
    {
        $queues = [];
        foreach ($names as $name) {
            $queue = $this->createStub(QueueConfigItemInterface::class);
            $queue->method('getName')->willReturn($name);
            $queues[] = $queue;
        }
        $this->topologyConfig->expects($this->once())->method('getQueues')->willReturn($queues);
    }

    /**
     * @param string[] $existing
     * @return void
     */
    private function expectSelectReturning(array $existing): void
    {
        $select = $this->createMock(Select::class);
        $this->connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with(self::QUEUE_TABLE, 'name')->willReturnSelf();
        $this->connection->expects($this->once())->method('fetchCol')->with($select)->willReturn($existing);
    }
}
