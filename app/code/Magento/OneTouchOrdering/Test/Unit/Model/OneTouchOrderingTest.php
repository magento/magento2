<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\Braintree\Gateway\Config\Config as BrainTreeConfig;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\OneTouchOrdering\Model\Config;
use Magento\OneTouchOrdering\Model\CustomerCreditCardManager;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;
use Magento\OneTouchOrdering\Model\RateCheck;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class OneTouchOrderingTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Customer
     */
    private $customer;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $oneTouchConfig;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $brainTreeConfig;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $rateCheck;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCreditCardManager;
    /**
     * @var OneTouchOrdering
     */
    private $oneTouchOrdering;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->customer = $this->createMock(Customer::class);
        $this->customerCreditCardManager = $this->createMock(CustomerCreditCardManager::class);
        $this->oneTouchConfig = $this->createMock(Config::class);
        $this->brainTreeConfig = $this->createMock(BrainTreeConfig::class);
        $this->rateCheck = $this->createMock(RateCheck::class);

        $this->oneTouchOrdering = $objectManager->getObject(
            OneTouchOrdering::class,
            [
                'oneTouchHelper' => $this->oneTouchConfig,
                'brainTreeConfig'=> $this->brainTreeConfig,
                'rateCheck' => $this->rateCheck,
                'customerCreditCardManager' => $this->customerCreditCardManager
            ]
        );
    }

    public function testAllAvailable()
    {
        $customerId = 123;
        $addressMock = $this->createMock(Address::class);
        $this->customer
            ->expects($this->atLeastOnce())
            ->method('getDefaultShippingAddress')
            ->willReturn($addressMock);
        $this->customer
            ->expects($this->atLeastOnce())
            ->method('getDefaultBillingAddress')
            ->willReturn($addressMock);
        $this->rateCheck
            ->expects($this->once())
            ->method('getRatesForCustomerAddress')
            ->with($addressMock)
            ->willReturn(['test rate']);
        $this->oneTouchConfig->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->customer->method('getId')->willReturn($customerId);
        $this->customerCreditCardManager->expects($this->once())
            ->method('getVisibleAvailableTokens')
            ->with($customerId)
            ->willReturn(['test token']);

        $this->brainTreeConfig->expects($this->once())->method('isActive')->willReturn(true);

        $this->assertTrue($this->oneTouchOrdering->isAvailableForCustomer($this->customer));
    }

    public function testNotAllAvailable()
    {
        $customerId = 123;
        $addressMock = $this->createMock(Address::class);
        $this->customer->method('getDefaultShippingAddress')->willReturn($addressMock);
        $this->customer->method('getDefaultBillingAddress')->willReturn(false);

        $this->rateCheck->method('getRatesForCustomerAddress')->with($addressMock)->willReturn([]);
        $this->oneTouchConfig->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->customer->method('getId')->willReturn($customerId);
        $this->customerCreditCardManager
            ->method('getVisibleAvailableTokens')
            ->with($customerId)
            ->willReturn([]);

        $this->brainTreeConfig->expects($this->once())->method('isActive')->willReturn(true);

        $this->assertFalse($this->oneTouchOrdering->isAvailableForCustomer($this->customer));
    }
}
