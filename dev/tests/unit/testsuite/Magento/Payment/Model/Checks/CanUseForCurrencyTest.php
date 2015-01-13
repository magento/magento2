<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Checks;

class CanUseForCurrencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expected currency code
     */
    const EXPECTED_CURRENCY_CODE = 'US';

    /**
     * @var CanUseForCurrency
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new CanUseForCurrency();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $paymentMethod = $this->getMockBuilder(
            'Magento\Payment\Model\Checks\PaymentMethodChecksInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseForCurrency')->with(
            self::EXPECTED_CURRENCY_CODE
        )->will($this->returnValue($expectation));

        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $store = $this->getMockBuilder(
            'Magento\Store\Model\Store'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $store->expects($this->once())->method('getBaseCurrencyCode')->will(
            $this->returnValue(self::EXPECTED_CURRENCY_CODE)
        );
        $quoteMock->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->assertEquals($expectation, $this->_model->isApplicable($paymentMethod, $quoteMock));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
