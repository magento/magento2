<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class PreventClearCheckoutSessionObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Observer\PreventClearCheckoutSessionObserver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $actionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerMock;

    protected function setUp()
    {
        $eventMethods = ['getControllerAction', 'dispatch', '__wakeUp'];
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->sessionHelperMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->helperMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->eventMock = $this->getMock(\Magento\Framework\Event::class, $eventMethods, [], '', false);
        $this->actionMock = $this->getMock(\Magento\Persistent\Controller\Index::class, [], [], '', false);
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->model = new \Magento\Persistent\Observer\PreventClearCheckoutSessionObserver(
            $this->sessionHelperMock,
            $this->helperMock,
            $this->customerSessionMock
        );
    }

    public function testExecuteWhenSessionIsPersist()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->will($this->returnValue($this->actionMock));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(false));
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->actionMock->expects($this->once())->method('setClearCheckoutSession')->with(false);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersist()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->will($this->returnValue($this->actionMock));
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->will($this->returnValue(true));
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->will($this->returnValue(true));
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->will($this->returnValue(false));
        $this->actionMock->expects($this->once())->method('setClearCheckoutSession')->with(false);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenActionIsNotPersistent()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('getControllerAction');
        $this->sessionHelperMock->expects($this->never())->method('isPersistent');
        $this->actionMock->expects($this->never())->method('setClearCheckoutSession');
        $this->model->execute($this->observerMock);
    }
}
