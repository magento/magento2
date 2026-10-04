<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\CustomerData;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Paypal\CustomerData\BuyerCountry;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuyerCountryTest extends TestCase
{
    /**
     * @var CurrentCustomer|MockObject
     */
    private CurrentCustomer $currentCustomer;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var BuyerCountry
     */
    private BuyerCountry $buyerCountry;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->currentCustomer = $this->createMock(CurrentCustomer::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        // Provide a default country fallback for guests / no address scenarios
        $this->scopeConfig->method('getValue')
            ->with('general/country/default', ScopeInterface::SCOPE_STORE)
            ->willReturn('US');

        $this->buyerCountry = new BuyerCountry($this->currentCustomer, $this->scopeConfig);
    }

    /**
     * @return void
     */
    public function testGetSectionDataException(): void
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willThrowException(new NoSuchEntityException());

        // Fallback to store default country
        $this->assertEquals(['code' => 'US'], $this->buyerCountry->getSectionData());
    }

    /**
     * @return void
     */
    public function testGetSectionDataNoAddress(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn(null);
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        // Fallback to store default country
        $this->assertEquals(['code' => 'US'], $this->buyerCountry->getSectionData());
    }

    /**
     * @return void
     */
    public function testGetSectionDataShippingAddress(): void
    {
        $addressId = 1;
        $countryId = 'US';
        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($addressId);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn($addressId);
        $customer->expects($this->once())->method('getAddresses')
            ->willReturn([$address]);
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $this->assertEquals(['code' => $countryId], $this->buyerCountry->getSectionData());
    }

    /**
     * @return void
     */
    public function testGetSectionDataBillingAddress(): void
    {
        $billingAddressId = 2;
        $countryId = 'GB';
        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())
            ->method('getCountryId')
            ->willReturn($countryId);
        $address->expects($this->once())
            ->method('getId')
            ->willReturn($billingAddressId);

        $customer = $this->createMock(CustomerInterface::class);
        // getDefaultBilling() is called twice (condition and value part of ternary)
        $customer->expects($this->exactly(2))
            ->method('getDefaultBilling')
            ->willReturn($billingAddressId);
        // ensure else branch is not used
        $customer->expects($this->never())
            ->method('getDefaultShipping');
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$address]);

        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);

        $this->assertEquals(['code' => $countryId], $this->buyerCountry->getSectionData());
    }
}
