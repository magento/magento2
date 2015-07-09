<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Test\Unit\Model;

class TaxConfigProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxConfigMock;

    /**
     * @var \Magento\Tax\Model\TaxConfigProvider
     */
    protected $model;

    protected function setUp()
    {
        $this->taxHelperMock = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $this->taxConfigMock = $this->getMock('Magento\Tax\Model\Config', [], [], '', false);

        $this->model = new \Magento\Tax\Model\TaxConfigProvider($this->taxHelperMock, $this->taxConfigMock);
    }

    /**
     * @dataProvider getConfigDataProvider
     * @param array $expectedResult
     * @param int $cartShippingBoth
     * @param int $cartShippingExclTax
     * @param int $cartBothPrices
     * @param int $cartPriceExclTax
     * @param int $cartSubTotalBoth
     * @param int $cartSubTotalExclTax
     */
    public function testGetConfig(
        $expectedResult,
        $cartShippingBoth,
        $cartShippingExclTax,
        $cartBothPrices,
        $cartPriceExclTax,
        $cartSubTotalBoth,
        $cartSubTotalExclTax
    ) {
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingBoth')
            ->will($this->returnValue($cartShippingBoth));
        $this->taxConfigMock->expects($this->any())->method('displayCartShippingExclTax')
            ->will($this->returnValue($cartShippingExclTax));

        $this->taxHelperMock->expects($this->any())->method('displayCartBothPrices')
            ->will($this->returnValue($cartBothPrices));
        $this->taxHelperMock->expects($this->any())->method('displayCartPriceExclTax')
            ->will($this->returnValue($cartPriceExclTax));

        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalBoth')
            ->will($this->returnValue($cartSubTotalBoth));
        $this->taxConfigMock->expects($this->any())->method('displayCartSubtotalExclTax')
            ->will($this->returnValue($cartSubTotalExclTax));

        $this->taxHelperMock->expects(($this->any()))->method('displayShippingPriceExcludingTax')
            ->will($this->returnValue(1));
        $this->taxHelperMock->expects(($this->any()))->method('displayShippingBothPrices')
            ->will($this->returnValue(1));
        $this->taxHelperMock->expects(($this->any()))->method('displayFullSummary')
            ->will($this->returnValue(1));
        $this->taxConfigMock->expects(($this->any()))->method('displayCartTaxWithGrandTotal')
            ->will($this->returnValue(1));
        $this->taxConfigMock->expects(($this->any()))->method('displayCartZeroTax')
            ->will($this->returnValue(1));
        $this->assertEquals($expectedResult, $this->model->getConfig());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'both',
                    'reviewItemPriceDisplayMode' => 'both',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1
                ],
                'cartShippingBoth' => 1,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 1,
                'cartPriceExclTax' => 1,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 1
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'excluding',
                    'reviewTotalsDisplayMode' => 'excluding',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 1,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 1
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'including',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'including',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 0
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'including',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'including',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 0,
                'cartSubTotalExclTax' => 0
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'both',
                    'reviewItemPriceDisplayMode' => 'both',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1
                ],
                'cartShippingBoth' => 1,
                'cartShippingExclTax' => 0,
                'cartBothPrices' => 1,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0
            ],
            [
                'expectedResult' => [
                    'isDisplayShippingPriceExclTax' => 1,
                    'isDisplayShippingBothPrices' => 1,
                    'reviewShippingDisplayMode' => 'excluding',
                    'reviewItemPriceDisplayMode' => 'including',
                    'reviewTotalsDisplayMode' => 'both',
                    'includeTaxInGrandTotal' => 1,
                    'isFullTaxSummaryDisplayed' => 1,
                    'isZeroTaxDisplayed' => 1
                ],
                'cartShippingBoth' => 0,
                'cartShippingExclTax' => 1,
                'cartBothPrices' => 0,
                'cartPriceExclTax' => 0,
                'cartSubTotalBoth' => 1,
                'cartSubTotalExclTax' => 0
            ],
        ];
    }
}
