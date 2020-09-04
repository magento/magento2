<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\Apm\Deployments;
use Magento\NewRelicReporting\Model\Apm\DeploymentsFactory;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Observer\ReportSystemCacheFlushToNewRelic;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportSystemCacheFlushToNewRelicTest extends TestCase
{
    /**
     * @var ReportSystemCacheFlushToNewRelic
     */
    protected $model;

    /**
     * @var Config|MockObject
     */
    protected $config;

    /**
     * @var Session|MockObject
     */
    protected $backendAuthSession;

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
        $this->backendAuthSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
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
        $this->deploymentsFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->deploymentsModel);

        $this->model = new ReportSystemCacheFlushToNewRelic(
            $this->config,
            $this->backendAuthSession,
            $this->deploymentsFactory
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportSystemCacheFlushToNewRelicModuleDisabledFromConfig()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(false);

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled in config
     *
     * @return void
     */
    public function testReportSystemCacheFlushToNewRelic()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendAuthSession->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);
        $userMock->expects($this->once())
            ->method('getId')
            ->willReturn('2');
        $this->deploymentsFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->deploymentsModel);
        $this->deploymentsModel->expects($this->once())
            ->method('setDeployment')
            ->willReturnSelf();

        $this->model->execute($eventObserver);
    }
}
