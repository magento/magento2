<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Plugin\ConfigurableProduct\Pricing;

use Magento\Catalog\Pricing\Price\FinalPrice as CatalogFinalPrice;
use Magento\ConfigurableProduct\Pricing\Price\FinalPriceResolver as ConfigurableProductFinalPriceResolver;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\PriceInfo\Base as PriceInfo;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Tax\Pricing\Adjustment;
use Magento\Weee\Helper\Data as WeeeHelperData;
use Magento\Weee\Plugin\ConfigurableProduct\Pricing\FinalPriceResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for FinalPriceResolver plugin
 */
class FinalPriceResolverTest extends TestCase
{
    /**
     * @var FinalPriceResolver
     */
    private FinalPriceResolver $plugin;

    /**
     * @var WeeeHelperData|MockObject
     */
    private WeeeHelperData|MockObject $weeeHelperDataMock;

    /**
     * @var ConfigurableProductFinalPriceResolver|MockObject
     */
    private ConfigurableProductFinalPriceResolver|MockObject $subjectMock;

    /**
     * @var SaleableInterface|MockObject
     */
    private SaleableInterface|MockObject $productMock;

    /**
     * @var PriceInfo|MockObject
     */
    private PriceInfo|MockObject $priceInfoMock;

    /**
     * @var CatalogFinalPrice|MockObject
     */
    private CatalogFinalPrice|MockObject $finalPriceMock;

    /**
     * @var AmountInterface|MockObject
     */
    private AmountInterface|MockObject $amountMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->weeeHelperDataMock = $this->createMock(WeeeHelperData::class);
        $this->subjectMock = $this->createMock(ConfigurableProductFinalPriceResolver::class);
        $this->productMock = $this->createMock(SaleableInterface::class);
        $this->priceInfoMock = $this->createMock(PriceInfo::class);
        $this->finalPriceMock = $this->createMock(CatalogFinalPrice::class);
        $this->amountMock = $this->createMock(AmountInterface::class);

        $this->plugin = new FinalPriceResolver($this->weeeHelperDataMock);
    }

    /**
     * Test afterResolvePrice when WEEE display is enabled via isDisplayIncl
     *
     * @return void
     */
    #[DataProvider('pricesDataProvider')]
    public function testAfterResolvePriceWhenWeeeDisplayIsEnabledViaDisplayIncl($amount, $expected): void
    {
        $originalResult = 100.0;

        // Mock WEEE display settings - isDisplayIncl returns true
        $this->weeeHelperDataMock->expects($this->once())
            ->method('isDisplayIncl')
            ->willReturn(true);

        // Should not call isDisplayInclDesc since isDisplayIncl is already true
        $this->weeeHelperDataMock->expects($this->never())
            ->method('isDisplayInclDesc');

        // Mock product price info chain
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(CatalogFinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);

        $this->finalPriceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($this->amountMock);

        $this->amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($amount);

        // Execute
        $result = $this->plugin->afterResolvePrice(
            $this->subjectMock,
            $originalResult,
            $this->productMock
        );

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Test afterResolvePrice when WEEE display is enabled via isDisplayInclDesc
     *
     * @return void
     */
    #[DataProvider('pricesDataProvider')]
    public function testAfterResolvePriceWhenWeeeDisplayIsEnabledViaDisplayInclDesc($amount, $expected): void
    {
        $originalResult = 100.0;

        // Mock WEEE display settings - isDisplayIncl returns false, isDisplayInclDesc returns true
        $this->weeeHelperDataMock->expects($this->once())
            ->method('isDisplayIncl')
            ->willReturn(false);

        $this->weeeHelperDataMock->expects($this->once())
            ->method('isDisplayInclDesc')
            ->willReturn(true);

        // Mock product price info chain
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(CatalogFinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);

        $this->finalPriceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($this->amountMock);

        $this->amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($amount);

        // Execute
        $result = $this->plugin->afterResolvePrice(
            $this->subjectMock,
            $originalResult,
            $this->productMock
        );

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Test afterResolvePrice when WEEE display is disabled
     *
     * @return void
     */
    #[DataProvider('pricesDataProvider')]
    public function testAfterResolvePriceWhenWeeeDisplayIsDisabled($amount, $expected): void
    {
        $originalResult = 100.0;

        // Mock WEEE display settings - both return false
        $this->weeeHelperDataMock->expects($this->once())
            ->method('isDisplayIncl')
            ->willReturn(false);

        $this->weeeHelperDataMock->expects($this->once())
            ->method('isDisplayInclDesc')
            ->willReturn(false);

        // Mock product price info chain
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(CatalogFinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);

        // Should call getValue() without any argument (not getValue(Adjustment::ADJUSTMENT_CODE))
        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($amount);

        // Execute
        $result = $this->plugin->afterResolvePrice(
            $this->subjectMock,
            $originalResult,
            $this->productMock
        );

        // Assert
        $this->assertEquals($expected, $result);
    }

    /**
     * Test afterResolvePrice returns correct price when tax adjustment is excluded
     *
     * @return void
     */
    public function testAfterResolvePriceReturnsPriceWithoutTaxAdjustment(): void
    {
        $originalResult = 141.61; // Double-taxed price (incorrect)
        $priceWithoutTaxAdjustment = 100.0; // Base price without tax adjustment

        // Mock WEEE display enabled
        $this->weeeHelperDataMock->expects($this->once())
            ->method('isDisplayIncl')
            ->willReturn(true);

        // Mock product price info chain
        $this->productMock->expects($this->once())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(CatalogFinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);

        $this->finalPriceMock->expects($this->once())
            ->method('getAmount')
            ->willReturn($this->amountMock);

        $this->amountMock->expects($this->once())
            ->method('getValue')
            ->with(Adjustment::ADJUSTMENT_CODE)
            ->willReturn($priceWithoutTaxAdjustment);

        // Execute
        $result = $this->plugin->afterResolvePrice(
            $this->subjectMock,
            $originalResult,
            $this->productMock
        );

        // Assert - should return the price without tax adjustment, not the original result
        $this->assertEquals($priceWithoutTaxAdjustment, $result);
        $this->assertNotEquals($originalResult, $result);
    }

    /**
     * Test that original result parameter is not used when WEEE is enabled
     *
     * Verifies that the plugin completely overrides the result from the subject
     *
     * @return void
     */
    public function testOriginalResultIsIgnoredWhenWeeeEnabled(): void
    {
        $originalResult = 999.99;
        $actualPrice = 119.0;

        $this->weeeHelperDataMock->method('isDisplayIncl')->willReturn(true);

        $this->productMock->method('getPriceInfo')->willReturn($this->priceInfoMock);
        $this->priceInfoMock->method('getPrice')->willReturn($this->finalPriceMock);
        $this->finalPriceMock->method('getAmount')->willReturn($this->amountMock);
        $this->amountMock->method('getValue')->willReturn($actualPrice);

        $result = $this->plugin->afterResolvePrice(
            $this->subjectMock,
            $originalResult,
            $this->productMock
        );

        // The result should be the actual price from product, not the original result
        $this->assertEquals($actualPrice, $result);
        $this->assertNotEquals($originalResult, $result);
    }

    /**
     * Test that original result parameter is not used when WEEE is disabled
     *
     * Verifies that the plugin completely overrides the result from the subject
     *
     * @return void
     */
    public function testOriginalResultIsIgnoredWhenWeeeDisabled(): void
    {
        $originalResult = 999.99;
        $actualPrice = 100.0;

        $this->weeeHelperDataMock->method('isDisplayIncl')->willReturn(false);
        $this->weeeHelperDataMock->method('isDisplayInclDesc')->willReturn(false);

        $this->productMock->method('getPriceInfo')->willReturn($this->priceInfoMock);
        $this->priceInfoMock->method('getPrice')->willReturn($this->finalPriceMock);
        $this->finalPriceMock->method('getValue')->willReturn($actualPrice);

        $result = $this->plugin->afterResolvePrice(
            $this->subjectMock,
            $originalResult,
            $this->productMock
        );

        // The result should be the actual price from product, not the original result
        $this->assertEquals($actualPrice, $result);
        $this->assertNotEquals($originalResult, $result);
    }

    public static function pricesDataProvider(): array
    {
        return [
            [
                "amount" => 119.0,
                "expected" => 119.0
            ],
            [
                "amount" => "119.0",
                "expected" => 119.0
            ]
        ];
    }
}
