<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class MakePersistentQuoteGuestObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\MakePersistentQuoteGuestObserver
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionMock;

    protected function setUp(): void
    {
        $this->actionMock = $this->createMock(\Magento\Persistent\Controller\Index::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->eventManagerMock =
            $this->createPartialMock(\Magento\Framework\Event::class, ['getControllerAction', '__wakeUp']);
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->model = new \Magento\Persistent\Observer\MakePersistentQuoteGuestObserver(
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
