<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use Magento\NewRelicReporting\Model\Observer\ReportConcurrentAdminsToNewRelic;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportConcurrentAdminsToNewRelicTest extends TestCase
{
    /**
     * @var ReportConcurrentAdminsToNewRelic
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
     * @var NewRelicWrapper|MockObject
     */
    protected $newRelicWrapper;

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
            ->setMethods(['isLoggedIn', 'getUser'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder(NewRelicWrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['addCustomParameter'])
            ->getMock();

        $this->model = new ReportConcurrentAdminsToNewRelic(
            $this->config,
            $this->backendAuthSession,
            $this->newRelicWrapper
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportConcurrentAdminsToNewRelicModuleDisabledFromConfig()
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
     * Test case when user is not logged in
     *
     * @return void
     */
    public function testReportConcurrentAdminsToNewRelicUserIsNotLoggedIn()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->backendAuthSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(false);

        $this->model->execute($eventObserver);
    }

    /**
     * Test case when module is enabled and user is logged in
     *
     * @return void
     */
    public function testReportConcurrentAdminsToNewRelic()
    {
        /** @var Observer|MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->backendAuthSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $userMock = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->backendAuthSession->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);
        $this->newRelicWrapper->expects($this->exactly(3))
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }
}
