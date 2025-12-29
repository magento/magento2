<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\SessionFactory;
use Magento\Persistent\Observer\SetCheckoutSessionPersistentDataObserver;
use Magento\Persistent\Model\Session as PersistentSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\TestCase;

class SetCheckoutSessionPersistentDataObserverTest extends TestCase
{

    use MockCreationTrait;

    /**
     * @var SetCheckoutSessionPersistentDataObserver
     */
    private $model;

    /**
     * @var Data|MockObject
     */
    private $helperMock;

    /**
     * @var Session|MockObject
     */
    private $sessionHelperMock;

    /**
     * @var CheckoutSession|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var CustomerSession|MockObject
     */
    private $customerSessionMock;

    /**
     * @var PersistentSession|MockObject
     */
    private $persistentSessionMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var SessionFactory|MockObject
     */
    private $sessionFactory;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(Data::class);
        $this->sessionHelperMock = $this->createMock(Session::class);
        $this->checkoutSessionMock = $this->createMock(CheckoutSession::class);
        $this->customerSessionMock = $this->createMock(CustomerSession::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createPartialMock(Event::class, ['getData']);
        $this->persistentSessionMock = $this->createPartialMockWithReflection(
            PersistentSession::class,
            ['getCustomerId']
        );
        $this->customerRepositoryMock = $this->createMock(
            CustomerRepositoryInterface::class
        );
        $this->sessionFactory = $this->createMock(
            SessionFactory::class
        );
        $this->model = new SetCheckoutSessionPersistentDataObserver(
            $this->sessionHelperMock,
            $this->customerSessionMock,
            $this->helperMock,
            $this->customerRepositoryMock,
            $this->sessionFactory,
        );
    }

    /**
     * Test execute method when session is not persistent
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testExecuteWhenSessionIsNotPersistent()
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->checkoutSessionMock);
        $this->sessionHelperMock->expects($this->once())
            ->method('isPersistent')
            ->willReturn(false);
        $this->checkoutSessionMock->expects($this->never())
            ->method('setLoadInactive');
        $this->checkoutSessionMock->expects($this->never())
            ->method('setCustomerData');
        $this->model->execute($this->observerMock);
    }

    /**
     * Test execute method when session is persistent
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testExecute()
    {
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);
        $this->eventMock->expects($this->once())
            ->method('getData')
            ->willReturn($this->checkoutSessionMock);
        $this->sessionHelperMock->expects($this->exactly(2))
            ->method('isPersistent')
            ->willReturn(true);
        $this->customerSessionMock->expects($this->any())
            ->method('isLoggedIn')
            ->willReturn(false);
        $this->helperMock->expects($this->exactly(2))
            ->method('isShoppingCartPersist')
            ->willReturn(true);
        $this->persistentSessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(123);
        $this->sessionHelperMock->expects($this->once())
            ->method('getSession')
            ->willReturn($this->persistentSessionMock);
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn(true);
        $this->checkoutSessionMock->expects($this->never())
            ->method('setLoadInactive');
        $this->checkoutSessionMock->expects($this->once())
            ->method('setCustomerData');
        $this->model->execute($this->observerMock);
    }
}
