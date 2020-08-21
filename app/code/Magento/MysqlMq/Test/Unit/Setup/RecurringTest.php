<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface;
use Magento\Framework\MessageQueue\Topology\ConfigInterface as TopologyConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Setup\Recurring;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecurringTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var Recurring
     */
    private $model;

    /**
     * @var \Magento\Framework\MessageQueue\ConfigInterface|MockObject
     */
    private $messageQueueConfig;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->messageQueueConfig = $this->getMockBuilder(
            TopologyConfigInterface::class
        )
            ->getMockForAbstractClass();
        $this->model = $this->objectManager->getObject(
            Recurring::class,
            [
                'messageQueueConfig' => $this->messageQueueConfig,
            ]
        );
    }

    /**
     * Test for install method
     */
    public function testInstall()
    {
        for ($i = 1; $i <= 3; $i++) {
            $queue = $this->getMockForAbstractClass(QueueConfigItemInterface::class);
            $queue->expects($this->once())
                ->method('getName')
                ->willReturn('queue_name_' . $i);
            $queues[] = $queue;
        }

        $dbQueues = [
            'queue_name_1',
            'queue_name_2',
        ];
        $queuesToInsert = [
            2 => 'queue_name_3'
        ];
        $queueTableName = 'queue_table';

        $setup = $this->getMockBuilder(SchemaSetupInterface::class)
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(ModuleContextInterface::class)
            ->getMockForAbstractClass();

        $setup->expects($this->once())->method('startSetup')->willReturnSelf();
        $this->messageQueueConfig->expects($this->once())->method('getQueues')->willReturn($queues);
        $connection = $this->getMockBuilder(AdapterInterface::class)
            ->getMockForAbstractClass();
        $setup->expects($this->once())->method('getConnection')->willReturn($connection);
        $setup->expects($this->any())->method('getTable')->with('queue')->willReturn($queueTableName);
        $select = $this->getMockBuilder(Select::class)
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->once())->method('select')->willReturn($select);
        $select->expects($this->once())->method('from')->with($queueTableName, 'name')->willReturnSelf();
        $connection->expects($this->once())->method('fetchCol')->with($select)->willReturn($dbQueues);
        $connection->expects($this->once())->method('insertArray')->with($queueTableName, ['name'], $queuesToInsert);
        $setup->expects($this->once())->method('endSetup')->willReturnSelf();

        $this->model->install($setup, $context);
    }
}
