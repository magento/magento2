<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class RenewCookieObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\RenewCookieObserver
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->sessionFactoryMock =
            $this->createPartialMock(\Magento\Persistent\Model\SessionFactory::class, ['create']);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMethods = ['getRequest', '__wakeUp'];
        $this->eventManagerMock = $this->createPartialMock(\Magento\Framework\Event::class, $eventMethods);
        $this->sessionMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->model = new \Magento\Persistent\Observer\RenewCookieObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->customerSessionMock,
            $this->sessionFactoryMock
        );
    }

    public function testRenewCookie()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('customer_account_logout');
        $this->helperMock->expects($this->once())->method('getLifeTime')->willReturn(60);
        $this->customerSessionMock
            ->expects($this->once())->method('getCookiePath')->willReturn('path/cookie');
        $this->sessionFactoryMock
            ->expects($this->once())
            ->method('create')
            ->willReturn($this->sessionMock);
        $this->sessionMock->expects($this->once())->method('renewPersistentCookie')->with(60, 'path/cookie');
        $this->model->execute($this->observerMock);
    }

    public function testRenewCookieWhenCannotProcessPersistentData()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(false);
        $this->helperMock->expects($this->never())->method('isEnabled');

        $this->observerMock
            ->expects($this->never())
            ->method('getEvent');

        $this->model->execute($this->observerMock);
    }
}
