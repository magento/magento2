<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\SessionFactory;
use Magento\Persistent\Observer\RenewCookieObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RenewCookieObserverTest extends TestCase
{
    /**
     * @var RenewCookieObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $sessionFactoryMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $sessionMock;

    /**
     * @var MockObject
     */
    protected $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->sessionFactoryMock =
            $this->createPartialMock(SessionFactory::class, ['create']);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventManagerMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->sessionMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->model = new RenewCookieObserver(
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
