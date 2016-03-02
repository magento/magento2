<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Customer\Observer\CustomerInvalidPasswordObserver;

/**
 * Class CustomerInvalidPasswordObserverTest
 */
class CustomerInvalidPasswordObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Account manager
     *
     * @var \Magento\Customer\Helper\AccountManagement
     */
    protected $accountManagementHelperMock;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Customer\Model\Data\Customer
     */
    protected $customerData;

    /**
     * @var CustomerInvalidPasswordObserver
     */
    protected $observer;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->accountManagementHelperMock = $this->getMock(
            'Magento\Customer\Helper\AccountManagement',
            ['processCustomerLockoutData'],
            [],
            '',
            false
        );
        $this->customerData = $this->getMock(
            'Magento\Customer\Model\Data\Customer',
            ['getId'],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->setMethods(['get', 'save'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->observer = new CustomerInvalidPasswordObserver(
            $this->accountManagementHelperMock,
            $this->customerRepositoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $username = 'customer@example.com';
        $customerId = 1;
        $observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getData'], [], '', false);
        $observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($eventMock);
        $eventMock->expects($this->once())
            ->method('getData')
            ->with('username')
            ->willReturn($username);
        $this->customerRepositoryMock->expects($this->once())
            ->method('get')
            ->with($username)
            ->willReturn($this->customerData);
        $this->customerData->expects($this->exactly(2))->method('getId')->willReturn($customerId);
        $this->accountManagementHelperMock->expects($this->once())
            ->method('processCustomerLockoutData')
            ->with($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->customerData);
        $this->observer->execute($observerMock);
    }
}
