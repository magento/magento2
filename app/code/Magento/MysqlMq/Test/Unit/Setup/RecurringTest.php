<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\Queue\QueueConfigSynchronizer;
use Magento\MysqlMq\Setup\Recurring;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecurringTest extends TestCase
{
    /**
     * @var Recurring
     */
    private $model;

    /**
     * @var QueueConfigSynchronizer|MockObject
     */
    private $queueConfigSynchronizer;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->queueConfigSynchronizer = $this->createMock(QueueConfigSynchronizer::class);
        $this->model = $objectManager->getObject(
            Recurring::class,
            [
                'queueConfigSynchronizer' => $this->queueConfigSynchronizer,
            ]
        );
    }

    /**
     * Test for install method when missing queues exist.
     */
    public function testInstall(): void
    {
        $queuesToInsert = ['queue_name_3'];
        $queueTableName = 'queue_table';

        $setup = $this->createMock(SchemaSetupInterface::class);
        $context = $this->createMock(ModuleContextInterface::class);

        $setup->expects($this->once())->method('startSetup')->willReturnSelf();
        $this->queueConfigSynchronizer->expects($this->once())
            ->method('getMissingNames')
            ->willReturn($queuesToInsert);
        $connection = $this->createMock(AdapterInterface::class);
        $setup->expects($this->once())->method('getConnection')->willReturn($connection);
        $setup->expects($this->once())->method('getTable')->with('queue')->willReturn($queueTableName);
        $connection->expects($this->once())->method('insertArray')->with($queueTableName, ['name'], $queuesToInsert);
        $setup->expects($this->once())->method('endSetup')->willReturnSelf();

        $this->model->install($setup, $context);
    }

    /**
     * Test for install method when no queues are missing.
     */
    public function testInstallWithNoMissingQueues(): void
    {
        $setup = $this->createMock(SchemaSetupInterface::class);
        $context = $this->createMock(ModuleContextInterface::class);

        $setup->expects($this->once())->method('startSetup')->willReturnSelf();
        $this->queueConfigSynchronizer->expects($this->once())
            ->method('getMissingNames')
            ->willReturn([]);
        $setup->expects($this->never())->method('getConnection');
        $setup->expects($this->once())->method('endSetup')->willReturnSelf();

        $this->model->install($setup, $context);
    }
}
