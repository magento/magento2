<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Weee\Test\Unit\Pricing;

use \Magento\Weee\Pricing\Adjustment;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Weee\Helper\Data as WeeeHelper;

class AdjustmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adjustment
     */
    protected $adjustment;

    /**
     * @var \Magento\Weee\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $weeeHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var int
     */
    protected $sortOrder = 5;

    public function setUp()
    {
        $this->weeeHelper = $this->getMock('Magento\Weee\Helper\Data', [], [], '', false);
        $this->priceCurrencyMock = $this->getMock('\Magento\Framework\Pricing\PriceCurrencyInterface');
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->will($this->returnCallback(
                    function ($arg) {
                        return round($arg * 0.5, 2);
                    }
                )
            );
        $this->priceCurrencyMock->expects($this->any())
            ->method('convert')
            ->will($this->returnCallback(
                function ($arg) {
                    return $arg * 0.5;
                }
            )
            );

        $this->adjustment = new Adjustment($this->weeeHelper, $this->priceCurrencyMock, $this->sortOrder);
    }

    public function testGetAdjustmentCode()
    {
        $this->assertEquals(Adjustment::ADJUSTMENT_CODE, $this->adjustment->getAdjustmentCode());
    }

    public function testIsIncludedInBasePrice()
    {
        $this->assertFalse($this->adjustment->isIncludedInBasePrice());
    }

    /**
     * @dataProvider isIncludedInDisplayPriceDataProvider
     */
    public function testIsIncludedInDisplayPrice($expectedResult)
    {
        $displayTypes = [
            \Magento\Weee\Model\Tax::DISPLAY_INCL,
            \Magento\Weee\Model\Tax::DISPLAY_INCL_DESCR,
            \Magento\Weee\Model\Tax::DISPLAY_EXCL_DESCR_INCL,
        ];
        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with($displayTypes)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInDisplayPrice());
    }

    /**
     * @return array
     */
    public function isIncludedInDisplayPriceDataProvider()
    {
        return [[false], [true]];
    }

    /**
     * @param float $amount
     * @param float $amountOld
     * @param float $expectedResult
     * @dataProvider applyAdjustmentDataProvider
     */
    public function testApplyAdjustment($amount, $amountOld, $expectedResult)
    {
        $object = $this->getMockForAbstractClass('Magento\Framework\Pricing\SaleableInterface');

        $this->weeeHelper->expects($this->any())
            ->method('getAmountExclTax')
            ->will($this->returnValue($amountOld));

        $this->assertEquals($expectedResult, $this->adjustment->applyAdjustment($amount, $object));
    }

    /**
     * @return array
     */
    public function applyAdjustmentDataProvider()
    {
        return [
            [1.1, 2.4, 2.3],
            [0.0, 2.2, 1.1],
            [1.1, 0.0, 1.1],
        ];
    }

    /**
     * @dataProvider isExcludedWithDataProvider
     * @param string $adjustmentCode
     * @param bool $expectedResult
     */
    public function testIsExcludedWith($adjustmentCode, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->adjustment->isExcludedWith($adjustmentCode));
    }

    public function isExcludedWithDataProvider()
    {
        return [
            ['weee', true],
            ['tax', true],
            ['not_tax_and_not_weee', false]
        ];
    }

    /**
     * @dataProvider getSortOrderProvider
     * @param bool $isTaxable
     * @param int $expectedResult
     */
    public function testGetSortOrder($isTaxable, $expectedResult)
    {
        $this->weeeHelper->expects($this->any())
            ->method('isTaxable')
            ->will($this->returnValue($isTaxable));

        $this->assertEquals($expectedResult, $this->adjustment->getSortOrder());
    }

    public function getSortOrderProvider()
    {
        return [
            [true, $this->sortOrder],
            [false, $this->sortOrder]
        ];
    }
}
