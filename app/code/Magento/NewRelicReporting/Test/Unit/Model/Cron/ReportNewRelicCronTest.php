<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\NewRelicReporting\Model\Apm\Deployments;
use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Counter;
use Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron;
use Magento\NewRelicReporting\Model\CronEvent;
use Magento\NewRelicReporting\Model\CronEventFactory;
use Magento\NewRelicReporting\Model\Module\Collect;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportNewRelicCronTest extends TestCase
{
    /**
     * @var ReportNewRelicCron
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var Collect|MockObject
     */
    protected $collect;

    /**
     * @var Counter|MockObject
     */
    protected $counter;

    /**
     * @var CronEventFactory|MockObject
     */
    protected $cronEventFactory;

    /**
     * @var CronEvent|MockObject
     */
    protected $cronEventModel;

    /**
     * @var DeploymentsFactory|MockObject
     */
    protected $deploymentsFactory;

    /**
     * @var Deployments|MockObject
     */
    protected $deploymentsModel;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->collect = $this->getMockBuilder(Collect::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModuleData'])
            ->getMock();
        $this->counter = $this->getMockBuilder(Counter::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'getAllProductsCount',
                'getConfigurableCount',
                'getActiveCatalogSize',
                'getCategoryCount',
                'getWebsiteCount',
                'getStoreViewsCount',
                'getCustomerCount',
            ])
            ->getMock();
        $this->cronEventFactory = $this->getMockBuilder(CronEventFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->cronEventModel = $this->getMockBuilder(CronEvent::class)
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'sendRequest'])
            ->getMock();
        $this->deploymentsFactory = $this->getMockBuilder(
            DeploymentsFactory::class
        )->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->deploymentsModel = $this->getMockBuilder(Deployments::class)
            ->disableOriginalConstructor()
            ->setMethods(['setDeployment'])
            ->getMock();

        $this->cronEventFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->cronEventModel);
        $this->deploymentsFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->deploymentsModel);

        $this->model = new ReportNewRelicCron(
            $this->config,
            $this->collect,
            $this->counter,
            $this->cronEventFactory,
            $this->deploymentsFactory
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportNewRelicCronModuleDisabledFromConfig()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }

    /**
     * Test case when module is enabled
     *
     * @return void
     */
    public function testReportNewRelicCron()
    {
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->counter->expects($this->once())
            ->method('getAllProductsCount');
        $this->counter->expects($this->once())
            ->method('getConfigurableCount');
        $this->counter->expects($this->once())
            ->method('getActiveCatalogSize');
        $this->counter->expects($this->once())
            ->method('getCategoryCount');
        $this->counter->expects($this->once())
            ->method('getWebsiteCount');
        $this->counter->expects($this->once())
            ->method('getStoreViewsCount');
        $this->counter->expects($this->once())
            ->method('getCustomerCount');
        $this->cronEventModel->expects($this->once())
            ->method('addData')
            ->willReturnSelf();
        $this->cronEventModel->expects($this->once())
            ->method('sendRequest');

        $this->deploymentsModel->expects($this->any())
            ->method('setDeployment');

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }

    /**
     * Test case when module is enabled and request is failed
     */
    public function testReportNewRelicCronRequestFailed()
    {
        $this->expectException('Exception');
        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->counter->expects($this->once())
            ->method('getAllProductsCount');
        $this->counter->expects($this->once())
            ->method('getConfigurableCount');
        $this->counter->expects($this->once())
            ->method('getActiveCatalogSize');
        $this->counter->expects($this->once())
            ->method('getCategoryCount');
        $this->counter->expects($this->once())
            ->method('getWebsiteCount');
        $this->counter->expects($this->once())
            ->method('getStoreViewsCount');
        $this->counter->expects($this->once())
            ->method('getCustomerCount');
        $this->cronEventModel->expects($this->once())
            ->method('addData')
            ->willReturnSelf();
        $this->cronEventModel->expects($this->once())
            ->method('sendRequest');

        $this->cronEventModel->expects($this->once())->method('sendRequest')->willThrowException(new \Exception());

        $this->deploymentsModel->expects($this->any())
            ->method('setDeployment');

        $this->model->report();
    }
}
