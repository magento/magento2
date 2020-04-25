<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Observer\CustomerLoginSuccessObserver;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use PHPUnit\Framework\TestCase;

class CustomerLoginSuccessObserverTest extends TestCase
{
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authenticationMock;

    /**
     * @var Customer
     */
    protected $customerModelMock;

    /**
     * @var CustomerLoginSuccessObserver
     */
    protected $customerLoginSuccessObserver;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->authenticationMock = $this->getMockForAbstractClass(AuthenticationInterface::class);

        $this->customerModelMock = $this->createPartialMock(Customer::class, ['getId']);
        $this->customerLoginSuccessObserver = new CustomerLoginSuccessObserver(
            $this->authenticationMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $observerMock = $this->createMock(Observer::class);
        $eventMock = $this->createPartialMock(Event::class, ['getData']);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('getData')
            ->with('model')
            ->willReturn($this->customerModelMock);
        $this->customerModelMock->expects($this->once())
            ->method('getId')
            ->willReturn($customerId);
        $this->authenticationMock->expects($this->once())
            ->method('unlock')
            ->with($customerId);
        $this->customerLoginSuccessObserver->execute($observerMock);
    }
}
