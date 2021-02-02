<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

class PreventClearCheckoutSessionObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\PreventClearCheckoutSessionObserver
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
    protected $eventMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $actionMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerMock;

    protected function setUp(): void
    {
        $eventMethods = ['getControllerAction', 'dispatch', '__wakeUp'];
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->eventMock = $this->createPartialMock(\Magento\Framework\Event::class, $eventMethods);
        $this->actionMock = $this->createMock(\Magento\Persistent\Controller\Index::class);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
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
            ->willReturn($this->actionMock);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->helperMock->expects($this->never())->method('isShoppingCartPersist');
        $this->actionMock->expects($this->once())->method('setClearCheckoutSession')->with(false);
        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenShoppingCartIsPersist()
    {
        $this->eventMock
            ->expects($this->once())
            ->method('getControllerAction')
            ->willReturn($this->actionMock);
        $this->sessionHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->helperMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);
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
