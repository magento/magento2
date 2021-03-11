<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Observer;

/**
 * Class SetCheckoutSessionPersistentDataObserverTest
 */
class SetCheckoutSessionPersistentDataObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Persistent\Observer\SetCheckoutSessionPersistentDataObserver
     */
    private $model;

    /**
     * @var \Magento\Persistent\Helper\Data| \PHPUnit\Framework\MockObject\MockObject
     */
    private $helperMock;

    /**
     * @var \Magento\Persistent\Helper\Session| \PHPUnit\Framework\MockObject\MockObject
     */
    private $sessionHelperMock;

    /**
     * @var \Magento\Checkout\Model\Session| \PHPUnit\Framework\MockObject\MockObject
     */
    private $checkoutSessionMock;

    /**
     * @var \Magento\Customer\Model\Session| \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerSessionMock;

    /**
     * @var \Magento\Persistent\Model\Session| \PHPUnit\Framework\MockObject\MockObject
     */
    private $persistentSessionMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface| \PHPUnit\Framework\MockObject\MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    private $observerMock;

    /**
     * @var \Magento\Framework\Event|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->helperMock = $this->createMock(\Magento\Persistent\Helper\Data::class);
        $this->sessionHelperMock = $this->createMock(\Magento\Persistent\Helper\Session::class);
        $this->checkoutSessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);
        $this->customerSessionMock = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getData']);
        $this->persistentSessionMock = $this->createPartialMock(
            \Magento\Persistent\Model\Session::class,
            ['getCustomerId']
        );
        $this->customerRepositoryMock = $this->createMock(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->model = new \Magento\Persistent\Observer\SetCheckoutSessionPersistentDataObserver(
            $this->sessionHelperMock,
            $this->customerSessionMock,
            $this->helperMock,
            $this->customerRepositoryMock
        );
    }

    /**
     * Test execute method when session is not persistent
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
