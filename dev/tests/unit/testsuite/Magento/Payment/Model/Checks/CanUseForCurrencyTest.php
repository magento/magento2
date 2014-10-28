<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
