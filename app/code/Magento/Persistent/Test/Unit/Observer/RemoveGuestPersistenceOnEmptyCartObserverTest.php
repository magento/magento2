<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use \Magento\Persistent\Observer\RemoveGuestPersistenceOnEmptyCartObserver;

class RemoveGuestPersistenceOnEmptyCartObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RemoveGuestPersistenceOnEmptyCartObserver
     */
    protected $model;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentDataMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\QuoteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteManagerMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $observerMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartRepositoryMock;

    protected function setUp()
    {
        $this->persistentSessionMock = $this->getMock(\Magento\Persistent\Helper\Session::class, [], [], '', false);
        $this->sessionModelMock = $this->getMock(\Magento\Persistent\Model\Session::class, [], [], '', false);
        $this->persistentDataMock = $this->getMock(\Magento\Persistent\Helper\Data::class, [], [], '', false);
        $this->customerSessionMock = $this->getMock(\Magento\Customer\Model\Session::class, [], [], '', false);
        $this->quoteManagerMock = $this->getMock(\Magento\Persistent\Model\QuoteManager::class, [], [], '', false);
        $this->observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $this->cartRepositoryMock = $this->getMock(
            \Magento\Quote\Api\CartRepositoryInterface::class,
            [],
            [],
            '',
            false
        );

        $this->model = new RemoveGuestPersistenceOnEmptyCartObserver(
            $this->persistentSessionMock,
            $this->persistentDataMock,
            $this->quoteManagerMock,
            $this->customerSessionMock,
            $this->cartRepositoryMock
        );
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithLoggedInCustomer()
    {
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithNonPersistentShoppingCart()
    {
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithEmptyCart()
    {
        $customerId = 1;
        $emptyCount = 0;

        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $sessionMock = $this->getMock(\Magento\Persistent\Model\Session::class, ['getCustomerId'], [], '', false);
        $this->persistentSessionMock->expects($this->once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        /** @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject $quoteMock */
        $quoteMock = $this->getMockForAbstractClass(
            \Magento\Quote\Api\Data\CartInterface::class,
            [],
            '',
            false,
            false,
            true,
            ['setCustomerEmail', 'getAddressesCollection'],
            false
        );
        $this->cartRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getItemsCount')->willReturn($emptyCount);
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithNonexistentCart()
    {
        $customerId = 1;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException;

        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $sessionMock = $this->getMock(\Magento\Persistent\Model\Session::class, ['getCustomerId'], [], '', false);
        $this->persistentSessionMock->expects($this->once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willThrowException($exception);
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }
}
