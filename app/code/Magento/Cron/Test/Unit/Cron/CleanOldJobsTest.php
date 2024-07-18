<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cron\Test\Unit\Cron;

use Magento\Cron\Cron\CleanOldJobs;
use Magento\Cron\Model\DeadlockRetrierInterface;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResourceModel;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Config;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use PHPUnit\Framework\TestCase;

// phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting.Missing, because such comments would add no value.

class CleanOldJobsTest extends TestCase
{
    private CleanOldJobs $cleanOldJobs;
    private Config $configMock;
    private ScheduleFactory $scheduleFactoryMock;
    private DateTime $dateTimeMock;
    private ScheduleResourceModel $scheduleResourceMock;
    private DeadlockRetrierInterface $retrierMock;
    private Schedule $scheduleMock;
    private int $time = 1501538400;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dateTimeMock = $this->getMockBuilder(DateTime::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateTimeMock->method('gmtTimestamp')
            ->willReturn($this->time);

        $this->retrierMock = $this->getMockForAbstractClass(DeadlockRetrierInterface::class);

        $this->scheduleFactoryMock = $this->getMockBuilder(ScheduleFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduleMock = $this->getMockBuilder(Schedule::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduleResourceMock = $this->getMockBuilder(ScheduleResourceModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduleMock
            ->method('getResource')
            ->willReturn($this->scheduleResourceMock);

        $this->scheduleFactoryMock
            ->method('create')
            ->willReturn($this->scheduleMock);

        $this->cleanOldJobs = new CleanOldJobs(
            $this->configMock,
            $this->dateTimeMock,
            $this->retrierMock,
            $this->scheduleFactoryMock
        );
    }

    public function testSuccess(): void
    {
        $tableName = 'cron_schedule';

        $this->configMock->expects($this->once())
            ->method('get')
            ->with('system')
            ->willReturn([
                'history_success_lifetime' => 100,
                'history_failure_lifetime' => 200,
            ]);

        $connectionMock = $this->getMockForAbstractClass(AdapterInterface::class);

        $connectionMock->expects($this->once())
            ->method('delete')
            ->with($tableName, [
                 'scheduled_at < ?' => '$this->time - (86400 + (200 * 60))',
            ]);

        $connectionMock->method('formatDate')
            ->willReturnMap([
                [1501538400, true, '$this->time'],
                [1501538200, true, '$this->time - 200'],
                [1501526400, true, '$this->time - (200 * 60)'],
                [1501452000, true, '$this->time - 86400'],
                [1501451800, true, '$this->time - (86400 + 200)'],
                [1501440000, true, '$this->time - (86400 + (200 * 60))'],
            ]);

        $this->scheduleResourceMock->expects($this->once())
            ->method('getTable')
            ->with($tableName)
            ->willReturn($tableName);
        $this->scheduleResourceMock->expects($this->exactly(3))
            ->method('getConnection')
            ->willReturn($connectionMock);

        $this->retrierMock->expects($this->once())
            ->method('execute')
            ->willReturnCallback(
                function ($callback) {
                    return $callback();
                }
            );

        $this->cleanOldJobs->execute();
    }

    public function testNoActionWhenEmptyConfig(): void
    {
        $this->configMock->expects($this->once())
            ->method('get')
            ->with('system')
            ->willReturn([]);

        $this->scheduleFactoryMock->expects($this->never())->method('create');

        $this->cleanOldJobs->execute();
    }
}
