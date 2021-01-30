<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Controller\Index;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Observer\MakePersistentQuoteGuestObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MakePersistentQuoteGuestObserverTest extends TestCase
{
    /**
     * @var MakePersistentQuoteGuestObserver
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $customerSessionMock;

    /**
     * @var MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $observerMock;

    /**
     * @var MockObject
     */
    protected $checkoutSession;

    /**
     * @var CheckoutSession|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $actionMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->actionMock = $this->createMock(Index::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->checkoutSession = $this->createMock(CheckoutSession::class);
        $this->eventManagerMock =
            $this->getMockBuilder(Event::class)
                ->addMethods(['getControllerAction'])
                ->disableOriginalConstructor()
                ->getMock();
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->model = new MakePersistentQuoteGuestObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->customerSessionMock,
            $this->checkoutSession
        );
    }

    public function testExecute()
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->willReturn($this->actionMock);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->checkoutSession->expects($this->once())->method('clearQuote')->willReturnSelf();
        $this->checkoutSession->expects($this->once())->method('clearStorage')->willReturnSelf();
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersist()
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->willReturn($this->actionMock);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->checkoutSession->expects($this->once())->method('clearQuote')->willReturnSelf();
        $this->checkoutSession->expects($this->once())->method('clearStorage')->willReturnSelf();
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsNotPersist()
    {
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->willReturn($this->actionMock);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);
        $this->checkoutSession->expects($this->never())->method('clearQuote')->willReturnSelf();
        $this->checkoutSession->expects($this->never())->method('clearStorage')->willReturnSelf();
        $this->model->execute($this->observerMock);
    }
}
