<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MysqlMq\Test\Unit\Model\QueueConfig;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\MysqlMq\Model\Queue\QueueConfigSynchronizer;
use Magento\MysqlMq\Model\QueueConfig\ChangeDetector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChangeDetectorTest extends TestCase
{
    /**
     * @var ChangeDetector
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
        $this->queueConfigSynchronizer = $this->createMock(QueueConfigSynchronizer::class);
        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ChangeDetector::class,
            ['queueConfigSynchronizer' => $this->queueConfigSynchronizer]
        );
    }

    /**
     * @return void
     */
    public function testHasChangesWhenQueuesAreMissing(): void
    {
        $this->queueConfigSynchronizer->method('getMissingNames')->willReturn(['queue_new']);
        $this->assertTrue($this->model->hasChanges());
        $this->assertSame(['queue_new'], $this->model->getMissingQueues());
    }

    /**
     * @return void
     */
    public function testHasNoChangesWhenInSync(): void
    {
        $this->queueConfigSynchronizer->method('getMissingNames')->willReturn([]);
        $this->assertFalse($this->model->hasChanges());
        $this->assertSame([], $this->model->getMissingQueues());
    }
}
