<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Multishipping\Test\Unit\Block\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Multishipping\Block\Checkout\Shipping;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Shipping
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $multiShippingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelperMock;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface', [], [], '', false);
        $this->multiShippingMock =
            $this->getMock('Magento\Multishipping\Model\Checkout\Type\Multishipping', [], [], '', false);
        $this->priceCurrencyMock =
            $this->getMock('Magento\Framework\Pricing\PriceCurrencyInterface', [], [], '', false);
        $this->taxHelperMock = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $this->model = $objectManager->getObject(
            'Magento\Multishipping\Block\Checkout\Shipping',
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
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $this->multiShippingMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())
            ->method('getAllShippingAddresses')->will($this->returnValue(['expected array']));
        $this->assertEquals(['expected array'], $this->model->getAddresses());
    }

    public function testGetAddressShippingMethod()
    {
        $addressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['getShippingMethod', '__wakeup'],
            [],
            '',
            false
        );
        $addressMock->expects($this->once())
            ->method('getShippingMethod')->will($this->returnValue('expected shipping method'));
        $this->assertEquals('expected shipping method', $this->model->getAddressShippingMethod($addressMock));
    }

    public function testGetShippingRates()
    {
        $addressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['getGroupedAllShippingRates', '__wakeup'],
            [],
            '',
            false
        );

        $addressMock->expects($this->once())
            ->method('getGroupedAllShippingRates')->will($this->returnValue(['expected array']));
        $this->assertEquals(['expected array'], $this->model->getShippingRates($addressMock));
    }

    public function testGetCarrierName()
    {
        $carrierCode = 'some carrier code';
        $name = 'some name';
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with('carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)->will($this->returnValue($name));

        $this->assertEquals($name, $this->model->getCarrierName($carrierCode));
    }

    public function testGetCarrierNameWithEmptyName()
    {
        $carrierCode = 'some carrier code';
        $this->scopeConfigMock->expects($this->once())->method('getValue')->with('carriers/' . $carrierCode . '/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE)->will($this->returnValue(null));

        $this->assertEquals($carrierCode, $this->model->getCarrierName($carrierCode));
    }

    public function testGetShippingPrice()
    {
        $addressMock = $this->getMock(
            'Magento\Quote\Model\Quote\Address',
            ['getQuote', '__wakeup'],
            [],
            '',
            false
        );
        $quoteMock = $this->getMock('Magento\Quote\Model\Quote', [], [], '', false);
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
        $price = 100;
        $flag = true;
        $shippingPrice = 11.11;
        $this->taxHelperMock->expects($this->once())
            ->method('getShippingPrice')->with($price, $flag, $addressMock)->will($this->returnValue($shippingPrice));
        $addressMock->expects($this->once())->method('getQuote')->will($this->returnValue($quoteMock));
        $quoteMock->expects($this->once())->method('getStore')->will($this->returnValue($storeMock));

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
