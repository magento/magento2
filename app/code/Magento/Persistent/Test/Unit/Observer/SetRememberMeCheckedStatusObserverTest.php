<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class SetRememberMeCheckedStatusObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SetRememberMeCheckedStatusObserver
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
    protected $checkoutSessionMock;

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
    protected $requestMock;

    protected function setUp(): void
    {
        $this->requestMock = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $checkoutMethods = ['setRememberMeChecked', '__wakeUp'];
        $this->checkoutSessionMock = $this->createPartialMock(
            \Magento\Checkout\Model\Session::class,
            $checkoutMethods
        );
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMethods = ['getRequest', '__wakeUp'];
        $this->eventManagerMock = $this->createPartialMock(\Magento\Framework\Event::class, $eventMethods);
        $this->model = new \Magento\Persistent\Observer\SetRememberMeCheckedStatusObserver(
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
