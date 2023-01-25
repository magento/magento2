<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Multishipping\Block\Checkout\Shipping;
use Magento\Multishipping\Model\Checkout\Type\Multishipping;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Tax\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    /**
     * @var Shipping
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $multiShippingMock;

    /**
     * @var MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var MockObject
     */
    protected $taxHelperMock;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->multiShippingMock =
            $this->createMock(Multishipping::class);
        $this->priceCurrencyMock =
            $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->taxHelperMock = $this->createMock(Data::class);
        $this->model = $objectManager->getObject(
            Shipping::class,
            [
                'multishipping' => $this->multiShippingMock,
                'scopeConfig' => $this->scopeConfigMock,
                'priceCurrency' => $this->priceCurrencyMock,
                'taxHelper' => $this->taxHelperMock
            ]
        );
    }

    public function testGetAddresses()
    {
        $quoteMock = $this->createMock(Quote::class);
        $this->multiShippingMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())
            ->method('getAllShippingAddresses')->willReturn(['expected array']);
        $this->assertEquals(['expected array'], $this->model->getAddresses());
    }

    public function testGetAddressShippingMethod()
    {
        $addressMock = $this->createPartialMock(
            Address::class,
            ['getShippingMethod']
        );
        $addressMock->expects($this->once())
            ->method('getShippingMethod')->willReturn('expected shipping method');
        $this->assertEquals('expected shipping method', $this->model->getAddressShippingMethod($addressMock));
    }

    public function testGetShippingRates()
    {
        $addressMock = $this->createPartialMock(
            Address::class,
            ['getGroupedAllShippingRates']
        );

        $addressMock->expects($this->once())
            ->method('getGroupedAllShippingRates')->willReturn(['expected array']);
        $this->assertEquals(['expected array'], $this->model->getShippingRates($addressMock));
    }

    public function testGetCarrierName()
    {
        $carrierCode = 'some carrier code';
        $name = 'some name';
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'carriers/' . $carrierCode . '/title',
            ScopeInterface::SCOPE_STORE
        )->willReturn($name);

        $this->assertEquals($name, $this->model->getCarrierName($carrierCode));
    }

    public function testGetCarrierNameWithEmptyName()
    {
        $carrierCode = 'some carrier code';
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with(
            'carriers/' . $carrierCode . '/title',
            ScopeInterface::SCOPE_STORE
        )->willReturn(null);

        $this->assertEquals($carrierCode, $this->model->getCarrierName($carrierCode));
    }

    public function testGetShippingPrice()
    {
        $addressMock = $this->createPartialMock(Address::class, ['getQuote']);
        $quoteMock = $this->createMock(Quote::class);
        $storeMock = $this->createMock(Store::class);
        $price = 100;
        $flag = true;
        $shippingPrice = 11.11;
        $this->taxHelperMock->expects($this->once())
            ->method('getShippingPrice')->with($price, $flag, $addressMock)->willReturn($shippingPrice);
        $addressMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getStore')->willReturn($storeMock);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convertAndFormat')
            ->with(
                $shippingPrice,
                true,
                PriceCurrencyInterface::DEFAULT_PRECISION,
                $storeMock
            );

        $this->model->getShippingPrice($addressMock, $price, $flag);
    }
}
