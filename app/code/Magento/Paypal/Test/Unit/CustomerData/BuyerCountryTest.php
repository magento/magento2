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

use Magento\Customer\Api\AddressRepositoryInterface;
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
     * @var AddressRepositoryInterface|MockObject
     */
    private AddressRepositoryInterface $addressRepository;

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
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);

        $this->buyerCountry = new BuyerCountry($this->currentCustomer, $this->addressRepository);
    }

    /**
     * @return void
     */
    public function testGetSectionDataException(): void
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->exactly(2))
            ->method('getDefaultBilling')
            ->willReturn(1);
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);
        $this->addressRepository->expects($this->once())
            ->method('getById')
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
        $customer = $this->createMock(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getDefaultBilling')
            ->willReturn(null);
        $customer->expects($this->once())
            ->method('getDefaultShipping')
            ->willReturn(1);
        $this->currentCustomer->expects($this->once())
            ->method('getCustomer')
            ->willReturn($customer);
        $address = $this->createMock(AddressInterface::class);
        $address->expects($this->once())
            ->method('getCountryId')
            ->willReturn('US');
        $this->addressRepository->expects($this->once())->method('getById')->willReturn($address);

        $this->assertEquals(['code' => 'US'], $this->buyerCountry->getSectionData());
    }
}
