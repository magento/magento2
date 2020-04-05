<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\CanUseForCurrency;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;

class CanUseForCurrencyTest extends TestCase
{
    const EXPECTED_CURRENCY_CODE = 'US';

    /**
     * @var CanUseForCurrency
     */
    private $model;

    protected function setUp()
    {
        $this->model = new CanUseForCurrency();
    }

    /**
     * @dataProvider paymentMethodDataProvider
     * @param bool $expectation
     */
    public function testIsApplicable($expectation)
    {
        $paymentMethod = $this->getMockBuilder(
            MethodInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $paymentMethod->expects($this->once())->method('canUseForCurrency')->with(
            self::EXPECTED_CURRENCY_CODE
        )->will($this->returnValue($expectation));

        $quoteMock = $this->getMockBuilder(Quote::class)->disableOriginalConstructor()->setMethods(
            []
        )->getMock();
        $store = $this->getMockBuilder(
            Store::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $store->expects($this->once())->method('getBaseCurrencyCode')->will(
            $this->returnValue(self::EXPECTED_CURRENCY_CODE)
        );
        $quoteMock->expects($this->once())->method('getStore')->will($this->returnValue($store));

        $this->assertEquals($expectation, $this->model->isApplicable($paymentMethod, $quoteMock));
    }

    /**
     * @return array
     */
    public function paymentMethodDataProvider()
    {
        return [[true], [false]];
    }
}
