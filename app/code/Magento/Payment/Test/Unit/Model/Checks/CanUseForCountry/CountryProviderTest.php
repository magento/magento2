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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CountryProviderTest extends TestCase
{
    /**
     * @var CountryProvider
     */
    private $countryProvider;

    /**
     * @var Data|MockObject
     */
    private $directoryMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    protected function setUp()
    {
        $this->directoryMock = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultCountry'])
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBillingAddress', 'getShippingAddress'])
            ->getMock();

        $this->countryProvider = new CountryProvider($this->directoryMock);
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

        $this->quoteMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $this->quoteMock->expects(static::never())
            ->method('getShippingAddress');

        $address->expects(static::exactly(2))
            ->method('getCountry')
            ->willReturn('UK');
        $this->directoryMock->expects(static::never())
            ->method('getDefaultCountry');

        static::assertEquals('UK', $this->countryProvider->getCountry($this->quoteMock));
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

        $this->quoteMock->expects(static::never())
            ->method('getShippingAddress');
        $this->quoteMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($address);

        $address->expects(static::once())
            ->method('getCountry')
            ->willReturn(null);
        $this->directoryMock->expects(static::once())
            ->method('getDefaultCountry')
            ->willReturn('US');
        static::assertEquals('US', $this->countryProvider->getCountry($this->quoteMock));
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

        $this->quoteMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn(null);

        $this->quoteMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($address);

        $address->expects(static::exactly(2))
            ->method('getCountry')
            ->willReturn('CA');

        $this->directoryMock->expects(static::never())
            ->method('getDefaultCountry');

        static::assertEquals('CA', $this->countryProvider->getCountry($this->quoteMock));
    }
}
