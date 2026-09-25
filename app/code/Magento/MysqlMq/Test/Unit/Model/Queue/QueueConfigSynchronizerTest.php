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
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\Queue\QueueConfigSynchronizer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QueueConfigSynchronizerTest extends TestCase
{
    /**
     * @var QueueConfigSynchronizer
     */
    private $model;

    /**
     * @var TopologyConfigInterface|MockObject
     */
    private $topologyConfig;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnection;

    /**
     * @var AdapterInterface|MockObject
     */
    private $connection;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->topologyConfig = $this->createMock(TopologyConfigInterface::class);
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->connection = $this->createMock(AdapterInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            QueueConfigSynchronizer::class,
            [
                'topologyConfig' => $this->topologyConfig,
                'resourceConnection' => $this->resourceConnection,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetMissingNamesReturnsOnlyConfiguredQueuesAbsentFromDatabase(): void
    {
        $this->mockConfiguredQueues(['queue_a', 'queue_b', 'queue_c']);
        $this->mockDatabaseQueues(['queue_a', 'legacy_removed_queue']);

        $this->assertSame(['queue_b', 'queue_c'], $this->model->getMissingNames());
    }

    /**
     * @return void
     */
    public function testGetMissingNamesWhenInSync(): void
    {
        $this->mockConfiguredQueues(['queue_a', 'queue_b']);
        $this->mockDatabaseQueues(['queue_a', 'queue_b', 'legacy_removed_queue']);

        $this->assertSame([], $this->model->getMissingNames());
    }

    /**
     * @param string[] $names
     * @return void
     */
    private function mockConfiguredQueues(array $names): void
    {
        $queues = [];
        foreach ($names as $name) {
            $queue = $this->createMock(QueueConfigItemInterface::class);
            $queue->method('getName')->willReturn($name);
            $queues[] = $queue;
        }
        $this->topologyConfig->method('getQueues')->willReturn($queues);
    }

    /**
     * @param string[] $names
     * @return void
     */
    private function mockDatabaseQueues(array $names): void
    {
        $tableName = 'queue';
        $select = $this->createMock(Select::class);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->with('queue')->willReturn($tableName);
        $this->connection->method('select')->willReturn($select);
        $select->method('from')->with($tableName, ['name'])->willReturnSelf();
        $this->connection->method('fetchCol')->with($select)->willReturn($names);
    }
}
