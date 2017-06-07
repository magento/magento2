<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Observer;

use Magento\Customer\Model\AuthenticationInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Observer\CustomerLoginSuccessObserver;

/**
 * Class CustomerLoginSuccessObserverTest
 */
class CustomerLoginSuccessObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Authentication
     *
     * @var AuthenticationInterface
     */
    protected $authenticationMock;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModelMock;

    /**
     * @var CustomerLoginSuccessObserver
     */
    protected $customerLoginSuccessObserver;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->authenticationMock = $this->getMock(
            AuthenticationInterface::class,
            [],
            [],
            '',
            false
        );

        $this->customerModelMock = $this->getMock(
            \Magento\Customer\Model\Customer::class,
            ['getId'],
            [],
            '',
            false
        );
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
        $observerMock = $this->getMock(\Magento\Framework\Event\Observer::class, [], [], '', false);
        $eventMock = $this->getMock(\Magento\Framework\Event::class, ['getData'], [], '', false);
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
