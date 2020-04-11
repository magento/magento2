<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Observer\RemoveGuestPersistenceOnEmptyCartObserver;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveGuestPersistenceOnEmptyCartObserverTest extends TestCase
{
    /**
     * @var RemoveGuestPersistenceOnEmptyCartObserver
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
     * @var CartRepositoryInterface|MockObject
     */
    protected $cartRepositoryMock;

    protected function setUp(): void
    {
        $this->persistentSessionMock = $this->createMock(Session::class);
        $this->sessionModelMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->persistentDataMock = $this->createMock(Data::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->cartRepositoryMock = $this->createMock(
            CartRepositoryInterface::class
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
        $sessionMock = $this->createPartialMock(\Magento\Persistent\Model\Session::class, ['getCustomerId']);
        $this->persistentSessionMock->expects($this->once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        /** @var CartInterface|MockObject $quoteMock */
        $quoteMock = $this->getMockForAbstractClass(
            CartInterface::class,
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
        $exception = new NoSuchEntityException();

        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $sessionMock = $this->createPartialMock(\Magento\Persistent\Model\Session::class, ['getCustomerId']);
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
