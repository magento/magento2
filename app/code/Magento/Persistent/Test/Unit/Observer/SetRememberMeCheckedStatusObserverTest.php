<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Observer\SetRememberMeCheckedStatusObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetRememberMeCheckedStatusObserverTest extends TestCase
{
    /**
     * @var SetRememberMeCheckedStatusObserver
     */
    private $model;

    /**
     * @var MockObject
     */
    private $helperMock;

    /**
     * @var MockObject
     */
    private $sessionHelperMock;

    /**
     * @var MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var MockObject
     */
    private $observerMock;

    /**
     * @var MockObject
     */
    private $eventManagerMock;

    /**
     * @var MockObject
     */
    private $requestMock;

    protected function setUp()
    {
        $this->requestMock = $this->createMock(Http::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->checkoutSessionMock = $this->createPartialMock(
            Session::class,
            ['setRememberMeChecked', '__wakeUp']
        );
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventManagerMock = $this->createPartialMock(
            Event::class,
            ['getRequest', '__wakeUp']
        );
        $this->model = new SetRememberMeCheckedStatusObserver(
            $this->helperMock,
            $this->sessionHelperMock,
            $this->checkoutSessionMock
        );
    }

    public function testSetRememberMeCheckedStatusWhenPersistentDataCannotProcess()
    {
        $this->helperMock->expects($this->once())
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
        $this->helperMock->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->willReturn(true);

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('persistent_remember_me')
            ->willReturn($rememberMeCheckbox);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->requestMock->expects($this->once())
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
        $this->helperMock->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->willReturn(true);

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->requestMock->expects($this->once())
            ->method('getPost')
            ->with('persistent_remember_me')
            ->willReturn($rememberMeCheckbox);
        $this->sessionHelperMock
            ->expects($this->once())
            ->method('setRememberMeChecked')
            ->with((bool)$rememberMeCheckbox);
        $this->requestMock->expects($this->exactly(2))
            ->method('getFullActionName')
            ->willReturn('method_name');
        $this->checkoutSessionMock
            ->expects($this->never())
            ->method('setRememberMeChecked');
        $this->model->execute($this->observerMock);
    }

    public function testSetRememberMeCheckedStatusWhenRequestNotExist()
    {
        $this->helperMock->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->willReturn(true);
        $this->helperMock->expects($this->once())->method('isEnabled')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isRememberMeEnabled')->willReturn(true);

        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getRequest');
        $this->model->execute($this->observerMock);
    }
}
