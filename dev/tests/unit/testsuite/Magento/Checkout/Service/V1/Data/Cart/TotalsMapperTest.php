<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data\Cart;

class TotalsMapperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Checkout\Service\V1\Data\Cart\TotalsMapper
     */
    protected $mapper;

    protected function setUp()
    {
        $this->mapper = new \Magento\Checkout\Service\V1\Data\Cart\TotalsMapper();
    }

    public function testMap()
    {
        $methods = ['getBaseGrandTotal', 'getGrandTotal', 'getBaseSubtotal', 'getSubtotal','getBaseCurrencyCode',
            'getBaseSubtotalWithDiscount', 'getSubtotalWithDiscount', 'getShippingAddress', '__wakeUp',
            'getQuoteCurrencyCode', ];
        $quoteMock = $this->getMock('Magento\Sales\Model\Quote', $methods, [], '', false);
        $methods = [
            'getDiscountAmount', 'getBaseDiscountAmount', 'getShippingAmount', 'getBaseShippingAmount',
            'getShippingDiscountAmount', 'getBaseShippingDiscountAmount', 'getTaxAmount', 'getBaseTaxAmount',
            'getShippingTaxAmount', 'getBaseShippingTaxAmount', 'getSubtotalInclTax', 'getBaseSubtotalTotalInclTax',
            'getShippingInclTax', 'getBaseShippingInclTax', 'getId', '__wakeUp',
        ];

        $shippingAddressMock = $this->getMock('\Magento\Sales\Model\Quote\Address', $methods, [], '', false);

        $quoteMock->expects($this->any())->method('getShippingAddress')
            ->will($this->returnValue($shippingAddressMock));

        $expected = [
            Totals::BASE_GRAND_TOTAL => 100,
            Totals::GRAND_TOTAL => 150,
            Totals::BASE_SUBTOTAL => 150,
            Totals::SUBTOTAL => 150,
            Totals::BASE_SUBTOTAL_WITH_DISCOUNT => 120,
            Totals::SUBTOTAL_WITH_DISCOUNT => 120,
            Totals::BASE_CURRENCY_CODE => 'EUR',
            Totals::QUOTE_CURRENCY_CODE => 'BR',
            Totals::DISCOUNT_AMOUNT => 110,
            Totals::BASE_DISCOUNT_AMOUNT => 110,
            Totals::SHIPPING_AMOUNT => 20,
            Totals::BASE_SHIPPING_AMOUNT => 20,
            Totals::SHIPPING_DISCOUNT_AMOUNT => 5,
            Totals::BASE_SHIPPING_DISCOUNT_AMOUNT => 5,
            Totals::TAX_AMOUNT => 3,
            Totals::BASE_TAX_AMOUNT => 3,
            Totals::SHIPPING_TAX_AMOUNT => 1,
            Totals::BASE_SHIPPING_TAX_AMOUNT => 1,
            Totals::SUBTOTAL_INCL_TAX => 153,
            Totals::BASE_SUBTOTAL_INCL_TAX => 153,
            Totals::SHIPPING_INCL_TAX => 21,
            Totals::BASE_SHIPPING_INCL_TAX => 21,
        ];
        $expectedQuoteMethods = [
            'getBaseGrandTotal' => 100,
            'getGrandTotal' => 150,
            'getBaseSubtotal' => 150,
            'getSubtotal' => 150,
            'getBaseSubtotalWithDiscount' => 120,
            'getSubtotalWithDiscount' => 120,
        ];

        $addressMethods = [
            'getDiscountAmount' => 110,
            'getBaseDiscountAmount' => 110,
            'getShippingAmount' => 20,
            'getBaseShippingAmount' => 20,
            'getShippingDiscountAmount' => 5,
            'getBaseShippingDiscountAmount' => 5,
            'getTaxAmount' => 3,
            'getBaseTaxAmount' => 3,
            'getShippingTaxAmount' => 1,
            'getBaseShippingTaxAmount' => 1,
            'getSubtotalInclTax' => 153,
            'getBaseSubtotalTotalInclTax' => 153,
            'getShippingInclTax' => 21,
            'getBaseShippingInclTax' => 21,
        ];

        $quoteMock->expects($this->atLeastOnce())->method('getBaseCurrencyCode')->will($this->returnValue('EUR'));
        $quoteMock->expects($this->atLeastOnce())->method('getQuoteCurrencyCode')->will($this->returnValue('BR'));

        foreach ($expectedQuoteMethods as $method => $value) {
            $quoteMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }
        foreach ($addressMethods as $method => $value) {
            $shippingAddressMock->expects($this->once())->method($method)->will($this->returnValue($value));
        }

        $this->assertEquals($expected, $this->mapper->map($quoteMock));
    }
}
