<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Pricing;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Weee\Helper\Data;
use Magento\Weee\Model\Tax;
use Magento\Weee\Pricing\Adjustment;
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
    protected $weeeHelper;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var int
     */
    protected static $sortOrder = 5;

    protected function setUp(): void
    {
        $this->weeeHelper = $this->createMock(Data::class);
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
        $this->priceCurrencyMock->expects($this->any())
            ->method('convertAndRound')
            ->willReturnCallback(
                function ($arg) {
                    return round($arg * 0.5, 2);
                }
            );
        $this->priceCurrencyMock->expects($this->any())
            ->method('convert')
            ->willReturnCallback(
                function ($arg) {
                    return $arg * 0.5;
                }
            );

        $this->adjustment = new Adjustment($this->weeeHelper, $this->priceCurrencyMock, self::$sortOrder);
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
            Tax::DISPLAY_INCL,
            Tax::DISPLAY_INCL_DESCR,
            Tax::DISPLAY_EXCL_DESCR_INCL,
        ];
        $this->weeeHelper->expects($this->any())
            ->method('typeOfDisplay')
            ->with($displayTypes)
            ->willReturn($expectedResult);

        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInDisplayPrice());
    }

    /**
     * @return array
     */
    public static function isIncludedInDisplayPriceDataProvider()
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
        $object = $this->getMockForAbstractClass(SaleableInterface::class);

        $this->weeeHelper->expects($this->any())
            ->method('getAmountExclTax')
            ->willReturn($amountOld);

        $this->assertEquals($expectedResult, $this->adjustment->applyAdjustment($amount, $object));
    }

    /**
     * @return array
     */
    public static function applyAdjustmentDataProvider()
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

    /**
     * @return array
     */
    public static function isExcludedWithDataProvider()
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
            ->willReturn($isTaxable);

        $this->assertEquals($expectedResult, $this->adjustment->getSortOrder());
    }

    /**
     * @return array
     */
    public static function getSortOrderProvider()
    {
        return [
            [true, self::$sortOrder],
            [false, self::$sortOrder]
        ];
    }
}
