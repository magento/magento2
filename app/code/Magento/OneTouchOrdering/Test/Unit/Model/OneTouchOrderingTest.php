<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OneTouchOrdering\Test\Unit\Model;

use Magento\OneTouchOrdering\Model\CustomerBrainTreeManager;
use Magento\OneTouchOrdering\Model\OneTouchOrdering;
use PHPUnit\Framework\TestCase;

class OneTouchOrderingTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customer;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $oneTouchConfig;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $brainTreeConfig;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $rateCheck;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerBrainTreeManager;
    /**
     * @var \Magento\OneTouchOrdering\Model\OneTouchOrdering
     */
    protected $oneTouchOrdering;

    public function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->customerSession = $this->createMock(\Magento\Customer\Model\Session::class);
        $this->customer = $this->createMock(\Magento\Customer\Model\Customer::class);
        $this->customerSession->method('getCustomer')->willReturn($this->customer);

        $this->customerBrainTreeManager = $this->createMock(CustomerBrainTreeManager::class);
        $this->oneTouchConfig = $this->createMock(\Magento\OneTouchOrdering\Model\Config::class);
        $this->brainTreeConfig = $this->createMock(\Magento\Braintree\Gateway\Config\Config::class);
        $this->rateCheck = $this->createMock(\Magento\OneTouchOrdering\Model\RateCheck::class);

        $this->oneTouchOrdering = $objectManager->getObject(
            \Magento\OneTouchOrdering\Model\OneTouchOrdering::class,
            [
                'customerSession' => $this->customerSession,
                'oneTouchHelper' => $this->oneTouchConfig,
                'brainTreeConfig'=> $this->brainTreeConfig,
                'rateCheck' => $this->rateCheck,
                'customerBrainTreeManager' => $this->customerBrainTreeManager
            ]
        );
    }

    public function testAllAvailable()
    {
        $customerId = 123;
        $addressMock = $this->createMock(\Magento\Customer\Model\Address::class);
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
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->oneTouchConfig->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->customerSession->method('getCustomerId')->willReturn($customerId);
        $this->customerBrainTreeManager->expects($this->once())
            ->method('getVisibleAvailableTokens')
            ->with($customerId)
            ->willReturn(['test token']);

        $this->brainTreeConfig->expects($this->once())->method('isActive')->willReturn(true);

        $this->assertTrue($this->oneTouchOrdering->isOneTouchOrderingAvailable());
    }

    public function testNotAllAvailable()
    {
        $customerId = 123;
        $addressMock = $this->createMock(\Magento\Customer\Model\Address::class);
        $this->customer->method('getDefaultShippingAddress')->willReturn($addressMock);
        $this->customer->method('getDefaultBillingAddress')->willReturn(false);

        $this->rateCheck->method('getRatesForCustomerAddress')->with($addressMock)->willReturn([]);
        $this->customerSession->expects($this->once())->method('isLoggedIn')->willReturn(true);
        $this->oneTouchConfig->expects($this->once())->method('isModuleEnabled')->willReturn(true);
        $this->customerSession->method('getCustomerId')->willReturn($customerId);
        $this->customerBrainTreeManager
            ->method('getVisibleAvailableTokens')
            ->with($customerId)
            ->willReturn([]);

        $this->brainTreeConfig->expects($this->once())->method('isActive')->willReturn(true);

        $this->assertFalse($this->oneTouchOrdering->isOneTouchOrderingAvailable());
    }
}
