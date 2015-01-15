<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Model\Checks;

class CanUseForCountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expected country id
     */
    const EXPECTED_COUNTRY_ID = 1;

    /**
     * @var CanUseForCountry
     */
    protected $_model;

    public function setUp()
    {
        $this->_model = new CanUseForCountry();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quoteMock = $this->getMockBuilder('Magento\Sales\Model\Quote')->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $billingAddressMock = $this->getMockBuilder(
            'Magento\Sales\Model\Quote\Address'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $billingAddressMock->expects($this->once())->method('getCountry')->will(
            $this->returnValue(self::EXPECTED_COUNTRY_ID)
        );
        $quoteMock->expects($this->once())->method('getBillingAddress')->will($this->returnValue($billingAddressMock));

        $paymentMethod = $this->getMockBuilder(
            'Magento\Payment\Model\Checks\PaymentMethodChecksInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseForCountry')->with(
            self::EXPECTED_COUNTRY_ID
        )->will($this->returnValue($expectation));

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
