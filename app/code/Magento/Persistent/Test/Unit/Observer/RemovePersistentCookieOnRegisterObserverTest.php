<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Observer\RemovePersistentCookieOnRegisterObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemovePersistentCookieOnRegisterObserverTest extends TestCase
{
    /**
     * @var RemovePersistentCookieOnRegisterObserver
     */
    protected $model;

    /**
     * @var Session|MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var Data|MockObject
     */
    protected $persistentDataMock;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    protected $customerSessionMock;

    /**
     * @var QuoteManager|MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var Observer|MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Persistent\Model\Session|MockObject
     */
    protected $sessionModelMock;

    protected function setUp(): void
    {
        $this->persistentSessionMock = $this->createMock(Session::class);
        $this->sessionModelMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->persistentDataMock = $this->createMock(Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->observerMock = $this->createMock(Observer::class);

        $this->model = new RemovePersistentCookieOnRegisterObserver(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->customerSessionMock,
            $this->quoteManagerMock
        );
    }

    public function testExecuteWithPersistentDataThatCanNotBeProcess()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->willReturn(false);
        $this->persistentSessionMock->expects($this->never())->method('getSession');

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->persistentSessionMock->expects($this->never())->method('getSession');

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithNotLoggedInCustomer()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionModelMock);
        $this->sessionModelMock->expects($this->once())->method('removePersistentCookie')->willReturnSelf();
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerId')->with(null)->willReturnSelf();
        $this->customerSessionMock->expects($this->once())
            ->method('setCustomerGroupId')->with(null)->willReturnSelf();
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }

    public function testExecute()
    {
        $this->persistentDataMock->expects($this->once())
            ->method('canProcess')->with($this->observerMock)->willReturn(true);
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->persistentSessionMock->expects($this->once())
            ->method('getSession')->willReturn($this->sessionModelMock);
        $this->sessionModelMock->expects($this->once())->method('removePersistentCookie')->willReturnSelf();
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->customerSessionMock->expects($this->never())->method('setCustomerId');
        $this->customerSessionMock->expects($this->never())->method('setCustomerGroupId');
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }
}
