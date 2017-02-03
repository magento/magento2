<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Test\Unit\Pricing;

use \Magento\Tax\Pricing\Adjustment;

use Magento\Framework\Pricing\SaleableInterface;

class AdjustmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Adjustment
     */
    protected $adjustment;

    /**
     * @var \Magento\Tax\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    /**
     * @var \Magento\Catalog\Helper\Data | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $catalogHelper;

    /**
     * @var int
     */
    protected $sortOrder = 5;

    public function setUp()
    {
        $this->taxHelper = $this->getMock('Magento\Tax\Helper\Data', [], [], '', false);
        $this->catalogHelper = $this->getMock('Magento\Catalog\Helper\Data', [], [], '', false);
        $this->adjustment = new Adjustment($this->taxHelper, $this->catalogHelper, $this->sortOrder);
    }

    public function testGetAdjustmentCode()
    {
        $this->assertEquals(Adjustment::ADJUSTMENT_CODE, $this->adjustment->getAdjustmentCode());
    }

    /**
     * @param bool $expectedResult
     * @dataProvider isIncludedInBasePriceDataProvider
     */
    public function testIsIncludedInBasePrice($expectedResult)
    {
        $this->taxHelper->expects($this->once())
            ->method('priceIncludesTax')
            ->will($this->returnValue($expectedResult));
        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInBasePrice());
    }

    public function isIncludedInBasePriceDataProvider()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider isIncludedInDisplayPriceDataProvider
     */
    public function testIsIncludedInDisplayPrice($displayPriceIncludingTax, $displayBothPrices, $expectedResult)
    {
        $this->taxHelper->expects($this->once())
            ->method('displayPriceIncludingTax')
            ->will($this->returnValue($displayPriceIncludingTax));
        if (!$displayPriceIncludingTax) {
            $this->taxHelper->expects($this->once())
                ->method('displayBothPrices')
                ->will($this->returnValue($displayBothPrices));
        }

        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInDisplayPrice());
    }

    /**
     * @return array
     */
    public function isIncludedInDisplayPriceDataProvider()
    {
        return [
            [false, false, false],
            [false, true, true],
            [true, false, true],
            [true, true, true],
        ];
    }

    /**
     * @param float $amount
     * @param bool $isPriceIncludesTax
     * @param float $price
     * @param float $expectedResult
     * @dataProvider extractAdjustmentDataProvider
     */
    public function testExtractAdjustment($isPriceIncludesTax, $amount, $price, $expectedResult)
    {
        $object = $this->getMockForAbstractClass('Magento\Framework\Pricing\SaleableInterface');

        $this->taxHelper->expects($this->any())
            ->method('priceIncludesTax')
            ->will($this->returnValue($isPriceIncludesTax));
        $this->catalogHelper->expects($this->any())
            ->method('getTaxPrice')
            ->with($object, $amount)
            ->will($this->returnValue($price));

        $this->assertEquals($expectedResult, $this->adjustment->extractAdjustment($amount, $object));
    }

    public function extractAdjustmentDataProvider()
    {
        return [
            [false, 'not_important', 'not_important', 0.00],
            [true, 10.1, 0.2, 9.9],
            [true, 10.1, 20.3, -10.2],
            [true, 0.0, 0.0, 0],
        ];
    }

    /**
     * @param bool $isPriceIncludesTax
     * @param float $amount
     * @param float $price
     * @param $expectedResult
     * @dataProvider applyAdjustmentDataProvider
     */
    public function testApplyAdjustment($amount, $price, $expectedResult)
    {
        $object = $this->getMockBuilder('Magento\Framework\Pricing\SaleableInterface')->getMock();

        $this->catalogHelper->expects($this->any())
            ->method('getTaxPrice')
            ->with($object, $amount, true)
            ->will($this->returnValue($price));

        $this->assertEquals($expectedResult, $this->adjustment->applyAdjustment($amount, $object));
    }

    /**
     * @return array
     */
    public function applyAdjustmentDataProvider()
    {
        return [
            [1.1, 2.2, 2.2],
            [0.0, 2.2, 2.2],
            [1.1, 0.0, 0.0],
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
            [Adjustment::ADJUSTMENT_CODE, true],
            ['not_tax', false]
        ];
    }

    public function testGetSortOrder()
    {
        $this->assertEquals($this->sortOrder, $this->adjustment->getSortOrder());
    }
}
