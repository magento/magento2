<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MysqlMq\Test\Unit\Setup;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class RecurringTest
 */
class RecurringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\MysqlMq\Setup\Recurring
     */
    private $model;

    /**
     * @var \Magento\Framework\MessageQueue\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $messageQueueConfig;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->messageQueueConfig = $this->getMockBuilder(
            \Magento\Framework\MessageQueue\Topology\ConfigInterface::class
        )
            ->getMockForAbstractClass();
        $this->model = $this->objectManager->getObject(
            \Magento\MysqlMq\Setup\Recurring::class,
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
            $queue = $this->createMock(\Magento\Framework\MessageQueue\Topology\Config\QueueConfigItemInterface::class);
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

        $setup = $this->getMockBuilder(\Magento\Framework\Setup\SchemaSetupInterface::class)
            ->getMockForAbstractClass();
        $context = $this->getMockBuilder(\Magento\Framework\Setup\ModuleContextInterface::class)
            ->getMockForAbstractClass();

        $setup->expects($this->once())->method('startSetup')->willReturnSelf();
        $this->messageQueueConfig->expects($this->once())->method('getQueues')->willReturn($queues);
        $connection = $this->getMockBuilder(\Magento\Framework\DB\Adapter\AdapterInterface::class)
            ->getMockForAbstractClass();
        $setup->expects($this->once())->method('getConnection')->willReturn($connection);
        $setup->expects($this->any())->method('getTable')->with('queue')->willReturn($queueTableName);
        $select = $this->getMockBuilder(\Magento\Framework\DB\Select::class)
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
