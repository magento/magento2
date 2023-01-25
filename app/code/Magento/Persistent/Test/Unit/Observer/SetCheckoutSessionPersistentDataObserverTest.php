<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Observer\SetCheckoutSessionPersistentDataObserver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SetCheckoutSessionPersistentDataObserverTest extends TestCase
{
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
     * @var \Magento\Checkout\Model\Session|MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \Magento\Customer\Model\Session|MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\Session|MockObject
     */
    private $persistentSessionMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

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
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->createPartialMock(Event::class, ['getData']);
        $this->persistentSessionMock = $this->getMockBuilder(\Magento\Persistent\Model\Session::class)
            ->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerRepositoryMock = $this->createMock(
            CustomerRepositoryInterface::class
        );
        $this->model = new SetCheckoutSessionPersistentDataObserver(
            $this->sessionHelperMock,
            $this->customerSessionMock,
            $this->helperMock,
            $this->customerRepositoryMock
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
        $this->customerSessionMock->expects($this->once())
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
            ->willReturn(true); //?
        $this->checkoutSessionMock->expects($this->never())
            ->method('setLoadInactive');
        $this->checkoutSessionMock->expects($this->once())
            ->method('setCustomerData');
        $this->model->execute($this->observerMock);
    }
}
