<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\NewRelicReporting\Model\Cron;

/**
 * Class CronTest
 */
class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\NewRelicReporting\Model\Cron
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\NewRelicReporting\Model\Cron\ReportModulesInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportModulesInfoMock;

    /**
     * @var \Magento\NewRelicReporting\Model\Cron\ReportCounts|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportCountsMock;

    /**
     * @var \Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportNewRelicCronMock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Config::class)
            ->setMethods(['isCronEnabled'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportModulesInfoMock = $this->getMockBuilder(
            \Magento\NewRelicReporting\Model\Cron\ReportModulesInfo::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->reportCountsMock = $this->getMockBuilder(\Magento\NewRelicReporting\Model\Cron\ReportCounts::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reportNewRelicCronMock = $this->getMockBuilder(
            \Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron::class
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
     *
     * @return \Magento\NewRelicReporting\Model\Cron
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
     *
     * @return \Magento\NewRelicReporting\Model\Cron
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
