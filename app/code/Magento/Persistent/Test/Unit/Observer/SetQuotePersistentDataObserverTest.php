<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

/**
 * Observer test for setting "is_persistent" value to quote
 */
class SetQuotePersistentDataObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SetQuotePersistentDataObserver
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
    protected $quoteMock;

    protected function setUp(): void
    {
        $quoteMethods = ['setIsActive', 'setIsPersistent', '__wakeUp'];
        $eventMethods = ['getQuote', '__wakeUp'];
        $this->quoteMock = $this->createPartialMock(\Magento\Quote\Model\Quote::class, $quoteMethods);
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->eventManagerMock = $this->createPartialMock(\Magento\Framework\Event::class, $eventMethods);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->model = new \Magento\Persistent\Observer\SetQuotePersistentDataObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->quoteManagerMock,
            $this->customerSessionMock
        );
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(false);
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenQuoteNotExist()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock->expects($this->once())->method('getQuote');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsPersistent()
    {
        $this->sessionHelperMock->expects($this->exactly(2))->method('isPersistent')->willReturn(true);
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventManagerMock);
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn($this->quoteMock);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->quoteMock->expects($this->once())->method('setIsPersistent')->with(true);
        $this->model->execute($this->observerMock);
    }
}
