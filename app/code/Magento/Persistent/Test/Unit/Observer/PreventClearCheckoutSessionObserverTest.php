<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Persistent\Controller\Index;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session as PersistentSessionHelper;
use Magento\Persistent\Observer\PreventClearCheckoutSessionObserver;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

class PreventClearCheckoutSessionObserverTest extends TestCase
{

    use MockCreationTrait;

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
        $this->customerSessionMock = $this->createMock(Session::class);
        $this->sessionHelperMock = $this->createMock(PersistentSessionHelper::class);
        $this->helperMock = $this->createMock(Data::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getControllerAction', 'dispatch']
        );
        $this->actionMock = $this->createMock(Index::class);
        $this->observerMock->expects($this->once())->method('getEvent')->willReturn($this->eventMock);
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
