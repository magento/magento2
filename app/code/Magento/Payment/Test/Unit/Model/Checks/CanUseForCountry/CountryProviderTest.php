<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
class CountryProviderTest extends \PHPUnit\Framework\TestCase
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

        $this->quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $this->quote->expects($this->never())
            ->method('getShippingAddress');

        $address->expects($this->exactly(2))
            ->method('getCountry')
            ->willReturn('UK');
        $this->directory->expects($this->never())
            ->method('getDefaultCountry');

        $this->assertEquals('UK', $this->countryProvider->getCountry($this->quote));
    }

    /**
     * @covers \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider::getCountry
     */
    public function testGetCountryForBillingAddressWithoutCountry()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountry'])
            ->getMock();

        $this->quote->expects($this->never())
            ->method('getShippingAddress');
        $this->quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $address->expects($this->once())
            ->method('getCountry')
            ->willReturn(null);
        $this->directory->expects($this->once())
            ->method('getDefaultCountry')
            ->willReturn('US');
        $this->assertEquals('US', $this->countryProvider->getCountry($this->quote));
    }

    /**
     * @covers \Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider::getCountry
     */
    public function testGetCountryShippingAddress()
    {
        $address = $this->getMockBuilder(Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCountry'])
            ->getMock();

        $this->quote->expects($this->once())
            ->method('getBillingAddress')
            ->willReturn(null);

        $this->quote->expects($this->once())
            ->method('getShippingAddress')
            ->willReturn($address);

        $address->expects($this->exactly(2))
            ->method('getCountry')
            ->willReturn('CA');

        $this->directory->expects($this->never())
            ->method('getDefaultCountry');

        $this->assertEquals('CA', $this->countryProvider->getCountry($this->quote));
    }
}
