<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Paypal\Test\Unit\CustomerData;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Paypal\CustomerData\BuyerCountry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BuyerCountryTest extends TestCase
{
    /**
     * @var CurrentCustomer|MockObject
     */
    private CurrentCustomer $currentCustomer;

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

        $this->buyerCountry = new BuyerCountry($this->currentCustomer);
    }

    /**
     * @return void
     */
    public function testGetSectionDataException(): void
    {
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willThrowException(new NoSuchEntityException());

        $this->assertEquals(['code' => null], $this->buyerCountry->getSectionData());
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

        $this->assertEquals(['code' => null], $this->buyerCountry->getSectionData());
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
}
