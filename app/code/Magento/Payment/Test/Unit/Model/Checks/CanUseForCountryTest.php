<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\CanUseForCountry;

class CanUseForCountryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Expected country id
     */
    const EXPECTED_COUNTRY_ID = 1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $countryProvider;

    /**
     * @var CanUseForCountry
     */
    protected $_model;

    public function setUp()
    {
        $this->countryProvider = $this->getMock(
            'Magento\Payment\Model\Checks\CanUseForCountry\CountryProvider',
            [],
            [],
            '',
            false,
            false
        );
        $this->_model = new CanUseForCountry($this->countryProvider);
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $quoteMock = $this->getMockBuilder('Magento\Quote\Model\Quote')->disableOriginalConstructor()->setMethods(
            []
        )->getMock();

        $paymentMethod = $this->getMockBuilder(
            '\Magento\Payment\Model\MethodInterface'
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseForCountry')->with(
            self::EXPECTED_COUNTRY_ID
        )->will($this->returnValue($expectation));
        $this->countryProvider->expects($this->once())->method('getCountry')->willReturn(self::EXPECTED_COUNTRY_ID);

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
