<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Setup;

use Magento\Framework\MessageQueue\Topology\ConfigInterface as MessageQueueConfig;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\MysqlMq\Model\Queue\Synchronizer;
use Magento\MysqlMq\Setup\Recurring;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RecurringTest extends TestCase
{
    /**
     * @var Synchronizer|MockObject
     */
    private $synchronizer;

    /**
     * @var Recurring
     */
    private $model;

    protected function setUp(): void
    {
        $this->synchronizer = $this->createMock(Synchronizer::class);
        $this->model = new Recurring($this->createStub(MessageQueueConfig::class), $this->synchronizer);
    }

    public function testInstall()
    {
        $setup = $this->createStub(SchemaSetupInterface::class);
        $context = $this->createStub(ModuleContextInterface::class);

        $this->synchronizer->expects($this->once())->method('synchronize')->willReturn(['Created queue "q".']);

        $this->model->install($setup, $context);
    }
}
