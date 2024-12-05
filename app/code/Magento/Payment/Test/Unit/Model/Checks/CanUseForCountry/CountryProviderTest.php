<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks\CanUseForCountry;

use Magento\Directory\Helper\Data;
use Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * CountryProviderTest contains tests for CountryProvider class
 */
class CountryProviderTest extends TestCase
{
    /**
     * @var CountryProvider
     */
    private $countryProvider;

    /**
     * @var Data|MockObject
     */
    private $directory;

    /**
     * @var Quote|MockObject
     */
    private $quote;

    protected function setUp(): void
    {
        $this->directory = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDefaultCountry'])
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBillingAddress', 'getShippingAddress'])
            ->getMock();

        $this->countryProvider = new CountryProvider($this->directory);
    }

    /**
     * @covers \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider::getCountry
     */
    public function testGetCountry()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountry'])
            ->getMock();

        $this->quote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $this->quote->expects(static::never())
            ->method('getShippingAddress');

        $address->expects(static::exactly(2))
            ->method('getCountry')
            ->willReturn('UK');
        $this->directory->expects(static::never())
            ->method('getDefaultCountry');

        static::assertEquals('UK', $this->countryProvider->getCountry($this->quote));
    }

    /**
     * @covers \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider::getCountry
     */
    public function testGetCountryForBillingAddressWithoutCountry()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountry'])
            ->getMock();

        $this->quote->expects(static::never())
            ->method('getShippingAddress');
        $this->quote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $address->expects(static::once())
            ->method('getCountry')
            ->willReturn(null);
        $this->directory->expects(static::once())
            ->method('getDefaultCountry')
            ->willReturn('US');
        static::assertEquals('US', $this->countryProvider->getCountry($this->quote));
    }

    /**
     * @covers \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider::getCountry
     */
    public function testGetCountryShippingAddress()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountry'])
            ->getMock();

        $this->quote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn(null);

        $this->quote->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($address);

        $address->expects(static::exactly(2))
            ->method('getCountry')
            ->willReturn('CA');

        $this->directory->expects(static::never())
            ->method('getDefaultCountry');

        static::assertEquals('CA', $this->countryProvider->getCountry($this->quote));
    }
}
