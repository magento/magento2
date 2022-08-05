<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Pricing;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Pricing\Adjustment;
use PHPUnit\Framework\MockObject\MockObject;

use PHPUnit\Framework\TestCase;

class AdjustmentTest extends TestCase
{
    /**
     * @var Adjustment
     */
    protected $adjustment;

    /**
     * @var Data|MockObject
     */
    protected $taxHelper;

    /**
     * @var \Magento\Catalog\Helper\Data|MockObject
     */
    protected $catalogHelper;

    /**
     * @var int
     */
    protected $sortOrder = 5;

    protected function setUp(): void
    {
        $this->taxHelper = $this->createMock(Data::class);
        $this->catalogHelper = $this->createMock(\Magento\Catalog\Helper\Data::class);
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
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInBasePrice());
    }

    /**
     * @return array
     */
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
            ->willReturn($displayPriceIncludingTax);
        if (!$displayPriceIncludingTax) {
            $this->taxHelper->expects($this->once())
                ->method('displayBothPrices')
                ->willReturn($displayBothPrices);
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
        $object = $this->getMockForAbstractClass(SaleableInterface::class);

        $this->taxHelper->expects($this->any())
            ->method('priceIncludesTax')
            ->willReturn($isPriceIncludesTax);
        $this->catalogHelper->expects($this->any())
            ->method('getTaxPrice')
            ->with($object, $amount)
            ->willReturn($price);

        $this->assertEquals($expectedResult, $this->adjustment->extractAdjustment($amount, $object));
    }

    /**
     * @return array
     */
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
        $object = $this->getMockBuilder(SaleableInterface::class)
            ->getMock();

        $this->catalogHelper->expects($this->any())
            ->method('getTaxPrice')
            ->with($object, $amount, true)
            ->willReturn($price);

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

    /**
     * @return array
     */
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
