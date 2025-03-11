<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Cron;
use Magento\NewRelicReporting\Model\Cron\ReportCounts;
use Magento\NewRelicReporting\Model\Cron\ReportModulesInfo;
use Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CronTest extends TestCase
{
    /**
     * @var Cron
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var ReportModulesInfo|MockObject
     */
    protected $reportModulesInfoMock;

    /**
     * @var ReportCounts|MockObject
     */
    protected $reportCountsMock;

    /**
     * @var ReportNewRelicCron|MockObject
     */
    protected $reportNewRelicCronMock;

    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['isCronEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportModulesInfoMock = $this->getMockBuilder(
            ReportModulesInfo::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->reportCountsMock = $this->getMockBuilder(ReportCounts::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportNewRelicCronMock = $this->getMockBuilder(
            ReportNewRelicCron::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->model = new Cron(
            $this->configMock,
            $this->reportModulesInfoMock,
            $this->reportCountsMock,
            $this->reportNewRelicCronMock
        );
    }

    /**
     * Test case when cron is disabled in config
     */
    public function testRunCronCronDisabledFromConfig()
    {
        $this->configMock->expects($this->once())
            ->method('isCronEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->runCron()
        );
    }

    /**
     * Test case when cron is enabled in config
     */
    public function testRunCronCronEnabledFromConfig()
    {
        $this->configMock->expects($this->once())
            ->method('isCronEnabled')
            ->willReturn(true);

        $this->reportModulesInfoMock->expects($this->once())
            ->method('report')
            ->willReturnSelf();
        $this->reportCountsMock->expects($this->once())
            ->method('report')
            ->willReturnSelf();
        $this->reportNewRelicCronMock->expects($this->once())
            ->method('report')
            ->willReturnSelf();

        $this->assertSame(
            $this->model,
            $this->model->runCron()
        );
    }
}
