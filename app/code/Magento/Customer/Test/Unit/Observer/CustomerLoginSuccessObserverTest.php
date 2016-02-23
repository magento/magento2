<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Customer\Observer\CustomerLoginSuccessObserver;

/**
 * Class CustomerLoginSuccessObserverTest
 */
class CustomerLoginSuccessObserverTest extends \PHPUnit_Framework_TestCase
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
    protected $customerDataMock;

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
        $this->accountManagementHelperMock = $this->getMock(
            'Magento\Customer\Helper\AccountManagement',
            ['processUnlockData'],
            [],
            '',
            false
        );
        $this->customerDataMock = $this->getMock(
            'Magento\Customer\Model\Data\Customer',
            ['getId'],
            [],
            '',
            false
        );
        $this->customerModelMock = $this->getMock(
            'Magento\Customer\Model\Customer',
            ['getId'],
            [],
            '',
            false
        );
        $this->customerRepositoryMock = $this->getMockBuilder('Magento\Customer\Api\CustomerRepositoryInterface')
            ->setMethods(['getById', 'save'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerLoginSuccessObserver = new CustomerLoginSuccessObserver(
            $this->accountManagementHelperMock,
            $this->customerRepositoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $customerId = 1;
        $observerMock = $this->getMock('Magento\Framework\Event\Observer', [], [], '', false);
        $eventMock = $this->getMock('Magento\Framework\Event', ['getData'], [], '', false);
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
        $this->customerRepositoryMock->expects($this->once())
            ->method('getById')
            ->willReturn($this->customerDataMock);
        $this->customerDataMock->expects($this->once())->method('getId')->willReturn($customerId);
        $this->accountManagementHelperMock->expects($this->once())
            ->method('processUnlockData')
            ->with($customerId);
        $this->customerRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->customerDataMock);
        $this->customerLoginSuccessObserver->execute($observerMock);
    }
}
