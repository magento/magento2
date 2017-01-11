<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class SetQuotePersistentDataObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SetQuotePersistentDataObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    protected function setUp()
    {
        $quoteMethods = ['setIsActive', 'setIsPersistent', '__wakeUp'];
        $eventMethods = ['getQuote', '__wakeUp'];
        $this->quoteMock = $this->getMock(\Magento\Quote\Model\Quote::class, $quoteMethods, [], '', false);
        $this->helperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->sessionHelperMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->eventManagerMock = $this->getMock(\Magento\Framework\Event::class, $eventMethods, [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->quoteManagerMock = $this->getMock(\Magento\Persistent\Model\QuoteManager::class, [], [], '', false);
        $this->model = new \Magento\Persistent\Observer\SetQuotePersistentDataObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->quoteManagerMock,
            $this->customerSessionMock
        );
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->observerMock->expects($this->never())->method('getEvent');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenQuoteNotExist()
    {
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock->expects($this->once())->method('getQuote');
        $this->customerSessionMock->expects($this->never())->method('isLoggedIn');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsPersistent()
    {
        $this->sessionHelperMock->expects($this->exactly(2))->method('isPersistent')->will($this->returnValue(true));
        $this->observerMock
            ->expects($this->once())
            ->method('getEvent')
            ->will($this->returnValue($this->eventManagerMock));
        $this->eventManagerMock
            ->expects($this->once())
            ->method('getQuote')
            ->will($this->returnValue($this->quoteMock));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(false));
        $this->quoteManagerMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->quoteMock->expects($this->once())->method('setIsPersistent')->with(true);
        $this->model->execute($this->observerMock);
    }
}
