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
use Magento\Persistent\Observer\SetRememberMeCheckedStatusObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetRememberMeCheckedStatusObserverTest extends TestCase
{
    /**
     * @var SetRememberMeCheckedStatusObserver
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
    protected $checkoutSessionMock;

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
    protected $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->addMethods(['setRememberMeChecked'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventManagerMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = new SetRememberMeCheckedStatusObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->checkoutSessionMock
        );
    }

    public function testSetRememberMeCheckedStatusWhenPersistentDataCannotProcess()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(false);
        $this->helperMock->expects($this->never())->method('isEnabled');
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenPersistentDataCanProcess()
    {
        $rememberMeCheckbox = 1;
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->willReturn(true);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('persistent_remember_me')
            ->willReturn($rememberMeCheckbox);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn('checkout_onepage_saveBilling');
        $this->checkoutSessionMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenActionNameIncorrect()
    {
        $rememberMeCheckbox = 1;
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->willReturn(true);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->requestMock
            ->expects($this->once())
            ->method('getPost')
            ->with('persistent_remember_me')
            ->willReturn($rememberMeCheckbox);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getFullActionName')
            ->willReturn('method_name');
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('setRememberMeChecked');
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenRequestNotExist()
    {
        $this->helperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->willReturn(true);

        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest');
        $this->model->execute($this->observerMock);
    }
}
