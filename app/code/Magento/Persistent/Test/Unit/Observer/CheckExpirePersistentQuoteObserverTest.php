<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class CheckExpirePersistentQuoteObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentHelperMock;

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

    protected function setUp()
    {
        $this->sessionMock = $this->getMock('Magento\Persistent\Helper\Session', [], [], '', false);
        $this->customerSessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->persistentHelperMock = $this->getMock('Magento\Persistent\Helper\Data', [], [], '', false);
        $this->observerMock
            = $this->getMock('Magento\Framework\Event\Observer', ['getControllerAction', '__wakeUp'], [], '', false);
        $this->quoteManagerMock = $this->getMock('Magento\Persistent\Model\QuoteManager', [], [], '', false);
        $this->eventManagerMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->checkoutSessionMock = $this->getMock('Magento\Checkout\Model\Session', [], [], '', false);
        $this->model = new \Magento\Persistent\Observer\CheckExpirePersistentQuoteObserver(
            $this->sessionMock,
            $this->persistentHelperMock,
            $this->quoteManagerMock,
            $this->eventManagerMock,
            $this->customerSessionMock,
            $this->checkoutSessionMock
        );
    }

    public function testExecuteWhenCanNotApplyPersistentData()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(false));
        $this->persistentHelperMock->expects($this->never())->method('isEnabled');
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenPersistentIsNotEnabled()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(false));
        $this->eventManagerMock->expects($this->never())->method('dispatch');
        $this->model->execute($this->observerMock);
    }
    public function testExecuteWhenPersistentIsEnabled()
    {
        $this->persistentHelperMock
            ->expects($this->once())
            ->method('canProcess')
            ->with($this->observerMock)
            ->will($this->returnValue(true));
        $this->persistentHelperMock->expects($this->once())->method('isEnabled')->will($this->returnValue(true));
        $this->sessionMock->expects($this->once())->method('isPersistent')->will($this->returnValue(false));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->checkoutSessionMock->expects($this->once())->method('getQuoteId')->will($this->returnValue(10));
        $this->observerMock->expects($this->once())->method('getControllerAction');
        $this->eventManagerMock->expects($this->once())->method('dispatch');
        $this->quoteManagerMock->expects($this->once())->method('expire');
        $this->customerSessionMock
            ->expects($this->once())
            ->method('setCustomerId')
            ->with(null)
            ->will($this->returnSelf());
        $this->model->execute($this->observerMock);
    }
}
