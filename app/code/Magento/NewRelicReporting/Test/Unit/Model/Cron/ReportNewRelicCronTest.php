<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Cron;

use Magento\NewRelicReporting\Model\Cron\ReportNewRelicCron;

/**
 * Class ReportNewRelicCronTest
 */
class ReportNewRelicCronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportNewRelicCron
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\NewRelicReporting\Model\Module\Collect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collect;

    /**
     * @var \Magento\NewRelicReporting\Model\Counter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $counter;

    /**
     * @var \Magento\NewRelicReporting\Model\CronEventFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cronEventFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\CronEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cronEventModel;

    /**
     * @var \Magento\NewRelicReporting\Model\Apm\DeploymentsFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentsFactory;

    /**
     * @var \Magento\NewRelicReporting\Model\Apm\Deployments|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentsModel;

    /**
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->collect = $this->getMockBuilder('Magento\NewRelicReporting\Model\Module\Collect')
            ->disableOriginalConstructor()
            ->setMethods(['getModuleData'])
            ->getMock();
        $this->counter = $this->getMockBuilder('Magento\NewRelicReporting\Model\Counter')
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
        $this->cronEventFactory = $this->getMockBuilder('Magento\NewRelicReporting\Model\CronEventFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->cronEventModel = $this->getMockBuilder('Magento\NewRelicReporting\Model\CronEvent')
            ->disableOriginalConstructor()
            ->setMethods(['addData', 'sendRequest'])
            ->getMock();
        $this->deploymentsFactory = $this->getMockBuilder('Magento\NewRelicReporting\Model\Apm\DeploymentsFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->deploymentsModel = $this->getMockBuilder('Magento\NewRelicReporting\Model\Apm\Deployments')
            ->disableOriginalConstructor()
            ->setMethods(['setDeployment'])
            ->getMock();
        $this->logger = $this->getMockBuilder('Psr\Log\LoggerInterface')
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
            $this->deploymentsFactory,
            $this->logger
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
        $testModuleData = [
            'changes' => [
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'enabled'],
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'disabled'],
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'installed'],
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'uninstalled'],
            ],
            'enabled' => 1,
            'disabled' => 1,
            'installed' => 1,
        ];

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->willReturn($testModuleData);
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
     *
     * @return void
     */
    public function testReportNewRelicCronRequestFailed()
    {
        $testModuleData = [
            'changes' => [
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'enabled'],
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'disabled'],
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'installed'],
                ['name' => 'name', 'setup_version' => '2.0.0', 'type' => 'uninstalled'],
            ],
            'enabled' => 1,
            'disabled' => 1,
            'installed' => 1,
        ];

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->collect->expects($this->once())
            ->method('getModuleData')
            ->willReturn($testModuleData);
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

        $this->cronEventModel->expects($this->once())->method('sendRequest')->willThrowException(
            new \Exception()
        );
        $this->logger->expects($this->once())->method('critical');

        $this->deploymentsModel->expects($this->any())
            ->method('setDeployment');

        $this->assertSame(
            $this->model,
            $this->model->report()
        );
    }
}
