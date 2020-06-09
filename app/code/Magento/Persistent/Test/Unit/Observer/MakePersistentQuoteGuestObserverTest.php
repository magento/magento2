<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Controller\Index;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
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
    protected $quoteManagerMock;

    /**
     * @var MockObject
     */
    protected $eventManagerMock;

    /**
     * @var MockObject
     */
    protected $actionMock;

    protected function setUp(): void
    {
        $this->actionMock = $this->createMock(Index::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
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
            $this->quoteManagerMock
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
        $this->quoteManagerMock->expects($this->once())->method('setGuest')->with(true);
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
        $this->quoteManagerMock->expects($this->once())->method('setGuest')->with(true);
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
        $this->quoteManagerMock->expects($this->never())->method('setGuest');
        $this->model->execute($this->observerMock);
    }
}
