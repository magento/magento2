<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Checks\CanUseForCountry;

use Magento\Directory\Helper\Data;
use Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * CountryProviderTest contains tests for CountryProvider class
 */
class CountryProviderTest extends \PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->directory = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultCountry'])
            ->getMock();

        $this->quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress', 'getShippingAddress'])
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
            ->setMethods(['getCountry'])
            ->getMock();

        $this->quote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $this->quote->expects(static::never())
            ->method('getShippingAddress');

        $address->expects(static::once())
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
        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountry'])
            ->getMock();

        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountry'])
            ->getMock();

        $this->quote->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);
        $this->quote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $billingAddress->expects(static::once())
            ->method('getCountry')
            ->willReturn(null);

        $shippingAddress->expects(static::once())
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
        $shippingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountry'])
            ->getMock();

        $billingAddress = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountry'])
            ->getMock();

        $this->quote->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        $this->quote->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($shippingAddress);

        $shippingAddress->expects(static::once())
            ->method('getCountry')
            ->willReturn('CA');

        $shippingAddress->expects(static::once())
            ->method('getCountry')
            ->willReturn(null);

        $this->directory->expects(static::never())
            ->method('getDefaultCountry');

        static::assertEquals('CA', $this->countryProvider->getCountry($this->quote));
    }
}
