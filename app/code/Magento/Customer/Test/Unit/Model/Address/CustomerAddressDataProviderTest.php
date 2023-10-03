<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Address;

use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Address\CustomerAddressDataFormatter;
use Magento\Customer\Model\Address\CustomerAddressDataProvider;
use Magento\Customer\Model\Config\Share;
use Magento\Directory\Model\AllowedCountries;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerAddressDataProviderTest extends TestCase
{
    /**
     * @var CustomerAddressDataFormatter|MockObject
     */
    private CustomerAddressDataFormatter $customerAddressDataFormatter;

    /**
     * @var Share|MockObject
     */
    private Share $shareConfig;

    /**
     * @var AllowedCountries|MockObject
     */
    private AllowedCountries $allowedCountryReader;

    /**
     * @var CustomerAddressDataProvider
     */
    private CustomerAddressDataProvider $provider;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->customerAddressDataFormatter = $this->createMock(CustomerAddressDataFormatter::class);
        $this->shareConfig = $this->createMock(Share::class);
        $this->allowedCountryReader = $this->createMock(AllowedCountries::class);

        $this->provider = new CustomerAddressDataProvider(
            $this->customerAddressDataFormatter,
            $this->shareConfig,
            $this->allowedCountryReader
        );
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testGetAddressDataByCustomer(): void
    {
        $addressLimit = 1;
        $this->allowedCountryReader->expects($this->once())->method('getAllowedCountries')->willReturn(['1']);
        $this->customerAddressDataFormatter->expects($this->once())
            ->method('prepareAddress')
            ->willreturn([1]);
        $this->shareConfig->expects($this->any())->method('isGlobalScope')->willReturn(false);

        $viableAddress = $this->getMockForAbstractClass(AddressInterface::class);
        $viableAddress->expects($this->once())->method('getId')->willReturn(1);
        $faultyAddress = $this->getMockForAbstractClass(AddressInterface::class);

        $customer = $this->getMockForAbstractClass(CustomerInterface::class);
        $customer->expects($this->once())
            ->method('getAddresses')
            ->willReturn([$viableAddress, $faultyAddress]);

        $expectedResult = [
            '1' => [1]
        ];
        $this->assertSame($expectedResult, $this->provider->getAddressDataByCustomer($customer, $addressLimit));
    }
}
