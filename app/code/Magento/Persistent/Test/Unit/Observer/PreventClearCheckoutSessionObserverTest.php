<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use Magento\Persistent\Observer\PreventClearCheckoutSessionObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Customer\Model\Session;
use Magento\Persistent\Helper\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event;
use Magento\Persistent\Controller\Index;

class PreventClearCheckoutSessionObserverTest extends TestCase
{
    /**
     * @var PreventClearCheckoutSessionObserver
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
    protected $eventMock;

    /**
     * @var MockObject
     */
    protected $actionMock;

    /**
     * @var MockObject
     */
    protected $customerMock;

    protected function setUp(): void
    {
        $eventMethods = ['getControllerAction', 'dispatch', '__wakeUp'];
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createPartialMock(Event::class, $eventMethods);
        $this->actionMock = $this->createMock(Index::class);
        $this->observerMock->expects($this->once())->method('getEvent')->will($this->returnValue($this->eventMock));
        $this->model = new PreventClearCheckoutSessionObserver(
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
