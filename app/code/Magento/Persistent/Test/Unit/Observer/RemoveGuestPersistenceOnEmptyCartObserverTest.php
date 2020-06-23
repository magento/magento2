<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Data as PersistentHelper;
use Magento\Persistent\Helper\Session as SessionHelper;
use Magento\Persistent\Model\QuoteManager;
use Magento\Persistent\Model\Session as PersistentSession;
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
    private $model;

    /**
     * @var SessionHelper|MockObject
     */
    private $persistentHelperMock;

    /**
     * @var PersistentSession|MockObject
     */
    private $sessionModelMock;

    /**
     * @var PersistentHelper|MockObject
     */
    private $persistentDataMock;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var QuoteManager|MockObject
     */
    private $quoteManagerMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var CartRepositoryInterface|MockObject
     */
    private $cartRepositoryMock;

    protected function setUp(): void
    {
        $this->persistentHelperMock = $this->createMock(SessionHelper::class);
        $this->sessionModelMock = $this->createMock(PersistentSession::class);
        $this->persistentDataMock = $this->createMock(PersistentHelper::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->quoteManagerMock = $this->createMock(QuoteManager::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->cartRepositoryMock = $this->createMock(
            CartRepositoryInterface::class
        );

        $this->model = new RemoveGuestPersistenceOnEmptyCartObserver(
            $this->persistentHelperMock,
            $this->persistentDataMock,
            $this->quoteManagerMock,
            $this->customerSessionMock,
            $this->cartRepositoryMock
        );
    }

    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->persistentHelperMock->expects($this->once())->method('isPersistent')->willReturn(false);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithLoggedInCustomer()
    {
        $this->persistentHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(true);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithNonPersistentShoppingCart()
    {
        $this->persistentHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(false);

        $this->model->execute($this->observerMock);
    }

    public function testExecuteWithEmptyCart()
    {
        $customerId = 1;
        $emptyCount = 0;

        $this->persistentHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $sessionMock = $this->getMockBuilder(PersistentSession::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->persistentHelperMock->expects($this->once())->method('getSession')->willReturn($sessionMock);
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

        $this->persistentHelperMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->customerSessionMock->expects($this->once())->method('isLoggedIn')->willReturn(false);
        $this->persistentDataMock->expects($this->once())->method('isShoppingCartPersist')->willReturn(true);
        $sessionMock = $this->getMockBuilder(PersistentSession::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->persistentHelperMock->expects($this->once())->method('getSession')->willReturn($sessionMock);
        $sessionMock->expects($this->once())->method('getCustomerId')->willReturn($customerId);
        $this->cartRepositoryMock->expects($this->once())
            ->method('getActiveForCustomer')
            ->with($customerId)
            ->willThrowException($exception);
        $this->quoteManagerMock->expects($this->once())->method('setGuest');

        $this->model->execute($this->observerMock);
    }
}
