<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use \Magento\Persistent\Observer\RemovePersistentCookieOnRegisterObserver;

class RemovePersistentCookieOnRegisterObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RemovePersistentCookieOnRegisterObserver
     */
    protected $model;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentDataMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\QuoteManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Persistent\Model\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $sessionModelMock;

    protected function setUp(): void
    {
        $this->persistentSessionMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->sessionModelMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->persistentDataMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(\Magento\Persistent\Model\QuoteManager::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);

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
