<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Json\EncoderInterface;
use Magento\NewRelicReporting\Model\Config;
use Magento\NewRelicReporting\Model\Observer\ReportConcurrentAdmins;
use Magento\NewRelicReporting\Model\Users;
use Magento\NewRelicReporting\Model\UsersFactory;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportConcurrentAdminsTest extends TestCase
{
    /**
     * @var ReportConcurrentAdmins
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
     * @var UsersFactory|MockObject
     */
    protected $usersFactory;

    /**
     * @var Users|MockObject
     */
    protected $usersModel;

    /**
     * @var EncoderInterface|MockObject
     */
    protected $jsonEncoder;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->backendAuthSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getUser'])
            ->onlyMethods(['isLoggedIn'])
            ->getMock();
        $this->usersFactory = $this->getMockBuilder(UsersFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->usersModel = $this->getMockBuilder(Users::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->jsonEncoder = $this->getMockBuilder(EncoderInterface::class)
            ->getMock();

        $this->usersFactory->expects($this->any())
            ->method('create')
            ->willReturn($this->usersModel);

        $this->model = new ReportConcurrentAdmins(
            $this->config,
            $this->backendAuthSession,
            $this->usersFactory,
            $this->jsonEncoder
        );
    }

    /**
     * Test case when module is disabled in config
     *
     * @return void
     */
    public function testReportConcurrentAdminsModuleDisabledFromConfig()
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
    public function testReportConcurrentAdminsUserIsNotLoggedIn()
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
    public function testReportConcurrentAdmins()
    {
        $testAction = 'JSON string';

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
        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->willReturn($testAction);
        $this->usersModel->expects($this->once())
            ->method('setData')
            ->with(['type' => 'admin_activity', 'action' => $testAction])
            ->willReturnSelf();
        $this->usersModel->expects($this->once())
            ->method('save');

        $this->model->execute($eventObserver);
    }
}
