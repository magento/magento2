<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model\Observer;

use Magento\NewRelicReporting\Model\Observer\ReportConcurrentAdminsToNewRelic;

/**
 * Class ReportConcurrentAdminsToNewRelicTest
 */
class ReportConcurrentAdminsToNewRelicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReportConcurrentAdminsToNewRelic
     */
    protected $model;

    /**
     * @var \Magento\NewRelicReporting\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * @var \Magento\Backend\Model\Auth\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $backendAuthSession;

    /**
     * @var \Magento\NewRelicReporting\Model\NewRelicWrapper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $newRelicWrapper;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->config = $this->getMockBuilder('Magento\NewRelicReporting\Model\Config')
            ->disableOriginalConstructor()
            ->setMethods(['isNewRelicEnabled'])
            ->getMock();
        $this->backendAuthSession = $this->getMockBuilder('Magento\Backend\Model\Auth\Session')
            ->disableOriginalConstructor()
            ->setMethods(['isLoggedIn', 'getUser'])
            ->getMock();
        $this->newRelicWrapper = $this->getMockBuilder('Magento\NewRelicReporting\Model\NewRelicWrapper')
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
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
        /** @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject $eventObserver */
        $eventObserver = $this->getMockBuilder('Magento\Framework\Event\Observer')
            ->disableOriginalConstructor()
            ->getMock();

        $this->config->expects($this->once())
            ->method('isNewRelicEnabled')
            ->willReturn(true);
        $this->backendAuthSession->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn(true);
        $userMock = $this->getMockBuilder('Magento\User\Model\User')->disableOriginalConstructor()->getMock();
        $this->backendAuthSession->expects($this->once())
            ->method('getUser')
            ->willReturn($userMock);
        $this->newRelicWrapper->expects($this->exactly(3))
            ->method('addCustomParameter')
            ->willReturn(true);

        $this->model->execute($eventObserver);
    }
}
