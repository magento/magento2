<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Pricing\Price\BundleSelectionPrice;
use Magento\Bundle\Pricing\Price\DiscountCalculator;
use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Framework\Event\Manager;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleSelectionPriceTest extends TestCase
{
    /**
     * @var BundleSelectionPrice
     */
    protected $selectionPrice;

    /**
     * @var CalculatorInterface|MockObject
     */
    protected $calculatorMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var Product|MockObject
     */
    protected $bundleMock;

    /**
     * @var Manager|MockObject
     */
    protected $eventManagerMock;

    /**
     * @var Base|MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice|MockObject
     */
    protected $finalPriceMock;

    /**
     * @var RegularPrice|MockObject
     */
    protected $regularPriceMock;

    /**
     * @var DiscountCalculator|MockObject
     */
    protected $discountCalculatorMock;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;

    /**
     * @var float
     */
    protected $quantity;

    /**
     * Test setUp
     */
    protected function setUp(): void
    {
        $this->productMock = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['getPriceInfo', 'getSelectionPriceType', 'getSelectionPriceValue', 'hasData', 'getData', 'getSelectionId']
        );

        $this->bundleMock = $this->createPartialMock(
            \Magento\Catalog\Test\Unit\Helper\ProductTestHelper::class,
            ['getPriceType', 'getPriceInfo', 'setFinalPrice', 'getData']
        );
        $this->calculatorMock = $this->createMock(CalculatorInterface::class);
        $this->eventManagerMock = $this->createPartialMock(Manager::class, ['dispatch']);
        $this->priceInfoMock = $this->createPartialMock(Base::class, ['getPrice']);
        $this->discountCalculatorMock = $this->createMock(DiscountCalculator::class);
        $this->finalPriceMock = $this->createMock(\Magento\Catalog\Pricing\Price\FinalPrice::class);
        $this->regularPriceMock = $this->createMock(RegularPrice::class);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceCurrencyMock = $this->createPartialMock(
            \Magento\Directory\Model\PriceCurrency::class,
            ['roundPrice', 'convert']
        );

        $this->quantity = 1;

        $this->setupSelectionPrice();
    }

    /**
     * @param bool $useRegularPrice
     */
    protected function setupSelectionPrice($useRegularPrice = false)
    {
        $this->selectionPrice = new BundleSelectionPrice(
            $this->productMock,
            $this->quantity,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->bundleMock,
            $this->eventManagerMock,
            $this->discountCalculatorMock,
            $useRegularPrice
        );
    }

    /**
     *  Test for method getValue with dynamic productType
     *
     * @param bool $useRegularPrice
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    #[DataProvider('useRegularPriceDataProvider')]
    public function testGetValueTypeDynamic($useRegularPrice)
    {
        $this->setupSelectionPrice($useRegularPrice);
        $priceCode = $useRegularPrice ? RegularPrice::PRICE_CODE : FinalPrice::PRICE_CODE;
        $regularPrice = 100.125;
        $discountedPrice = 70.453;
        $actualPrice = $useRegularPrice ? $regularPrice : $discountedPrice;
        $expectedPrice = $useRegularPrice ? round($regularPrice, 2) : round($discountedPrice, 2);

        $this->bundleMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(Price::PRICE_TYPE_DYNAMIC);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($priceCode)
            ->willReturn($this->finalPriceMock);
        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($actualPrice);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with(
                    $this->bundleMock,
                    $actualPrice
                )
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('roundPrice')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->selectionPrice->getValue());
    }

    /**
     * Test for method getValue with type Fixed and selectionPriceType not null.
     *
     * @param bool $useRegularPrice
     *
     * @return void
     */
    #[DataProvider('useRegularPriceDataProvider')]
    public function testGetValueTypeFixedWithSelectionPriceType(bool $useRegularPrice)
    {
        $this->setupSelectionPrice($useRegularPrice);
        $regularPrice = 100.125;
        $discountedPrice = 70.453;
        $actualPrice = $useRegularPrice ? $regularPrice : $discountedPrice;
        $expectedPrice = $useRegularPrice ? round($regularPrice, 2) : round($discountedPrice, 2);

        $this->bundleMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(Price::PRICE_TYPE_FIXED);
        $this->bundleMock->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(RegularPrice::PRICE_CODE)
            ->willReturn($this->regularPriceMock);
        $this->regularPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($actualPrice);
        $this->bundleMock->expects($this->once())
            ->method('setFinalPrice')
            ->willReturnSelf();
        $this->eventManagerMock->expects($this->once())
            ->method('dispatch');
        $this->bundleMock->expects($this->exactly(2))
            ->method('getData')
            ->willReturnMap(
                [
                    ['qty', null, 1],
                    ['final_price', null, 100],
                    ['price', null, 100],
                ]
            );
        $this->productMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn(true);
        $this->productMock->method('getSelectionPriceValue')->willReturn($actualPrice);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with($this->bundleMock, $actualPrice)
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('roundPrice')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->selectionPrice->getValue());
    }

    /**
     * test for method getValue with type Fixed and selectionPriceType is empty or zero
     *
     * @param bool $useRegularPrice
     */
    #[DataProvider('useRegularPriceDataProvider')]
    public function testGetValueTypeFixedWithoutSelectionPriceType($useRegularPrice)
    {
        $this->setupSelectionPrice($useRegularPrice);
        $regularPrice = 100.125;
        $discountedPrice = 70.453;
        $convertedValue = 100.247;
        $actualPrice = $useRegularPrice ? $convertedValue : $discountedPrice;
        $expectedPrice = $useRegularPrice ? round($convertedValue, 2) : round($discountedPrice, 2);

        $this->bundleMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(Price::PRICE_TYPE_FIXED);
        $this->productMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn(false);
        $this->productMock->method('getSelectionPriceValue')->willReturn($regularPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($regularPrice)
            ->willReturn($convertedValue);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with(
                    $this->bundleMock,
                    $convertedValue
                )
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('roundPrice')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->selectionPrice->getValue());
    }

    /**
     * test for method getValue with type Fixed and selectionPriceType is empty or zero
     *
     * @param bool $useRegularPrice
     */
    #[DataProvider('useRegularPriceDataProvider')]
    public function testFixedPriceWithMultipleQty($useRegularPrice)
    {
        $qty = 2;

        $selectionPrice = new BundleSelectionPrice(
            $this->productMock,
            $qty,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            $this->bundleMock,
            $this->eventManagerMock,
            $this->discountCalculatorMock,
            $useRegularPrice
        );

        $this->setupSelectionPrice($useRegularPrice);
        $regularPrice = 100.125;
        $discountedPrice = 70.453;
        $convertedValue = 100.247;
        $actualPrice = $useRegularPrice ? $convertedValue : $discountedPrice;
        $expectedPrice = $useRegularPrice ? round($convertedValue, 2) : round($discountedPrice, 2);

        $this->bundleMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(Price::PRICE_TYPE_FIXED);
        $this->productMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn(false);
        $this->productMock->method('getSelectionPriceValue')->willReturn($regularPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($regularPrice)
            ->willReturn($convertedValue);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with(
                    $this->bundleMock,
                    $convertedValue
                )
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('roundPrice')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $selectionPrice->getValue());
    }

    /**
     * @return array
     */
    public static function useRegularPriceDataProvider()
    {
        return [
            'useRegularPrice' => [
                true,
            ],
            'notUseRegularPrice' => [
                false,
            ],
        ];
    }

    public function testGetProductFixedBundle()
    {
        $this->bundleMock->method('getPriceType')->willReturn(Price::PRICE_TYPE_FIXED);
        $product = $this->selectionPrice->getProduct();
        $this->assertEquals($this->bundleMock, $product);
    }

    public function testGetProductDynamicBundle()
    {
        $this->bundleMock->method('getPriceType')->willReturn(Price::PRICE_TYPE_DYNAMIC);
        $product = $this->selectionPrice->getProduct();
        $this->assertEquals($this->productMock, $product);
    }

    public function testGetAmount()
    {
        $this->setupSelectionPrice();

        $price = 10.;
        $amount = 20.;

        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with(FinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);

        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($price);

        $this->discountCalculatorMock->expects($this->once())
            ->method('calculateDiscount')
            ->with($this->bundleMock, $price)
            ->willReturn($price);

        $this->priceCurrencyMock->expects($this->once())
            ->method('roundPrice')
            ->with($price)
            ->willReturn($price);

        $this->bundleMock->method('getPriceType')->willReturn(Price::PRICE_TYPE_DYNAMIC);

        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($price, $this->productMock, null)
            ->willReturn($amount);

        $this->assertEquals($amount, $this->selectionPrice->getAmount());
    }
}
