<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Pricing;

use Magento\Framework\Pricing\SaleableInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Pricing\Adjustment;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdjustmentTest extends TestCase
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

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

    public function testGetAdjustmentCode(): void
    {
        $this->assertEquals(Adjustment::ADJUSTMENT_CODE, $this->adjustment->getAdjustmentCode());
    }

    #[DataProvider('isIncludedInBasePriceDataProvider')]
    public function testIsIncludedInBasePrice(bool $expectedResult): void
    {
        $this->taxHelper->expects($this->once())
            ->method('priceIncludesTax')
            ->willReturn($expectedResult);
        $this->assertEquals($expectedResult, $this->adjustment->isIncludedInBasePrice());
    }

    /**
     * @return array
     */
    public static function isIncludedInBasePriceDataProvider(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('isIncludedInDisplayPriceDataProvider')]
    public function testIsIncludedInDisplayPrice(
        bool $displayPriceIncludingTax,
        bool $displayBothPrices,
        bool $expectedResult
    ): void {
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
    public static function isIncludedInDisplayPriceDataProvider(): array
    {
        return [
            [false, false, false],
            [false, true, true],
            [true, false, true],
            [true, true, true],
        ];
    }

    #[DataProvider('extractAdjustmentDataProvider')]
    public function testExtractAdjustment(bool $isPriceIncludesTax, $amount, $price, float $expectedResult): void
    {
        $object = $this->createMock(SaleableInterface::class);

        $this->taxHelper->expects($this->any())
            ->method('priceIncludesTax')
            ->willReturn($isPriceIncludesTax);
        $this->catalogHelper->expects($this->any())
            ->method('getTaxPrice')
            ->with($object, $amount)
            ->willReturn($price);

        $this->assertEqualsWithDelta(
            $expectedResult,
            $this->adjustment->extractAdjustment($amount, $object),
            self::EPSILON
        );
    }

    /**
     * @return array
     */
    public static function extractAdjustmentDataProvider(): array
    {
        return [
            [false, 'not_important', 'not_important', 0.00],
            [true, 10.1, 0.2, 9.9],
            [true, 10.1, 20.3, -10.2],
            [true, 0.0, 0.0, 0],
        ];
    }

    #[DataProvider('applyAdjustmentDataProvider')]
    public function testApplyAdjustment(float $amount, float $price, float $expectedResult): void
    {
        $object = $this->createMock(SaleableInterface::class);

        $this->catalogHelper->expects($this->any())
            ->method('getTaxPrice')
            ->with($object, $amount, true)
            ->willReturn($price);

        $this->assertEquals($expectedResult, $this->adjustment->applyAdjustment($amount, $object));
    }

    /**
     * @return array
     */
    public static function applyAdjustmentDataProvider(): array
    {
        return [
            [1.1, 2.2, 2.2],
            [0.0, 2.2, 2.2],
            [1.1, 0.0, 0.0],
        ];
    }

    #[DataProvider('isExcludedWithDataProvider')]
    public function testIsExcludedWith(string $adjustmentCode, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->adjustment->isExcludedWith($adjustmentCode));
    }

    /**
     * @return array
     */
    public static function isExcludedWithDataProvider(): array
    {
        return [
            [Adjustment::ADJUSTMENT_CODE, true],
            ['not_tax', false]
        ];
    }

    public function testGetSortOrder(): void
    {
        $this->assertEquals($this->sortOrder, $this->adjustment->getSortOrder());
    }
}
