<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\FinalPrice;
use Magento\Catalog\Pricing\Price\RegularPrice;

/**
 * Class BundleSelectionPriceTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundleSelectionPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleSelectionPrice
     */
    protected $selectionPrice;

    /**
     * @var \Magento\Framework\Pricing\Adjustment\CalculatorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $calculatorMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $bundleMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $eventManagerMock;

    /**
     * @var \Magento\Framework\Pricing\PriceInfo\Base|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceInfoMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\FinalPrice|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $finalPriceMock;

    /**
     * @var \Magento\Catalog\Pricing\Price\RegularPrice|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $regularPriceMock;

    /**
     * @var \Magento\Bundle\Pricing\Price\DiscountCalculator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $discountCalculatorMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
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
            \Magento\Catalog\Model\Product::class,
            ['__wakeup', 'getPriceInfo', 'getSelectionPriceType', 'getSelectionPriceValue']
        );

        $this->bundleMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeup', 'getPriceType', 'getPriceInfo', 'setFinalPrice', 'getData']
        );
        $this->calculatorMock = $this->getMockBuilder(\Magento\Framework\Pricing\Adjustment\CalculatorInterface::class)
            ->getMockForAbstractClass();
        $this->eventManagerMock = $this->createPartialMock(\Magento\Framework\Event\Manager::class, ['dispatch']);
        $this->priceInfoMock = $this->createPartialMock(\Magento\Framework\Pricing\PriceInfo\Base::class, ['getPrice']);
        $this->discountCalculatorMock = $this->createMock(\Magento\Bundle\Pricing\Price\DiscountCalculator::class);
        $this->finalPriceMock = $this->createMock(\Magento\Catalog\Pricing\Price\FinalPrice::class);
        $this->regularPriceMock = $this->createMock(\Magento\Catalog\Pricing\Price\RegularPrice::class);
        $this->productMock->expects($this->atLeastOnce())
            ->method('getPriceInfo')
            ->willReturn($this->priceInfoMock);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->quantity = 1;

        $this->setupSelectionPrice();
    }

    /**
     * @param bool $useRegularPrice
     */
    protected function setupSelectionPrice($useRegularPrice = false)
    {
        $this->selectionPrice = new \Magento\Bundle\Pricing\Price\BundleSelectionPrice(
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
     * @dataProvider useRegularPriceDataProvider
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
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
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);
        $this->priceInfoMock->expects($this->once())
            ->method('getPrice')
            ->with($this->equalTo($priceCode))
            ->willReturn($this->finalPriceMock);
        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($actualPrice);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with(
                    $this->equalTo($this->bundleMock),
                    $this->equalTo($actualPrice)
                )
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->selectionPrice->getValue());
    }

    /**
     * Test for method getValue with type Fixed and selectionPriceType not null.
     *
     * @param bool $useRegularPrice
     * @dataProvider useRegularPriceDataProvider
     *
     * @return void
     */
    public function testGetValueTypeFixedWithSelectionPriceType(bool $useRegularPrice)
    {
        $this->setupSelectionPrice($useRegularPrice);
        $regularPrice = 100.125;
        $discountedPrice = 70.453;
        $actualPrice = $useRegularPrice ? $regularPrice : $discountedPrice;
        $expectedPrice = $useRegularPrice ? round($regularPrice, 2) : round($discountedPrice, 2);

        $this->bundleMock->expects($this->once())
            ->method('getPriceType')
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
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
        $this->productMock->expects($this->any())
            ->method('getSelectionPriceValue')
            ->willReturn($actualPrice);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with($this->bundleMock, $actualPrice)
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->selectionPrice->getValue());
    }

    /**
     * test for method getValue with type Fixed and selectionPriceType is empty or zero
     *
     * @param bool $useRegularPrice
     * @dataProvider useRegularPriceDataProvider
     */
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
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $this->productMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn(false);
        $this->productMock->expects($this->any())
            ->method('getSelectionPriceValue')
            ->willReturn($regularPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($regularPrice)
            ->willReturn($convertedValue);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with(
                    $this->equalTo($this->bundleMock),
                    $this->equalTo($convertedValue)
                )
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $this->selectionPrice->getValue());
    }

    /**
     * test for method getValue with type Fixed and selectionPriceType is empty or zero
     *
     * @param bool $useRegularPrice
     * @dataProvider useRegularPriceDataProvider
     */
    public function testFixedPriceWithMultipleQty($useRegularPrice)
    {
        $qty = 2;

        $selectionPrice = new \Magento\Bundle\Pricing\Price\BundleSelectionPrice(
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
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $this->productMock->expects($this->once())
            ->method('getSelectionPriceType')
            ->willReturn(false);
        $this->productMock->expects($this->any())
            ->method('getSelectionPriceValue')
            ->willReturn($regularPrice);

        $this->priceCurrencyMock->expects($this->once())
            ->method('convert')
            ->with($regularPrice)
            ->willReturn($convertedValue);

        if (!$useRegularPrice) {
            $this->discountCalculatorMock->expects($this->once())
                ->method('calculateDiscount')
                ->with(
                    $this->equalTo($this->bundleMock),
                    $this->equalTo($convertedValue)
                )
                ->willReturn($discountedPrice);
        }

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($actualPrice)
            ->willReturn($expectedPrice);

        $this->assertEquals($expectedPrice, $selectionPrice->getValue());
    }

    /**
     * @return array
     */
    public function useRegularPriceDataProvider()
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
        $this->bundleMock->expects($this->any())
            ->method('getPriceType')
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $product = $this->selectionPrice->getProduct();
        $this->assertEquals($this->bundleMock, $product);
    }

    public function testGetProductDynamicBundle()
    {
        $this->bundleMock->expects($this->any())
            ->method('getPriceType')
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);
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
            ->with(\Magento\Bundle\Pricing\Price\FinalPrice::PRICE_CODE)
            ->willReturn($this->finalPriceMock);

        $this->finalPriceMock->expects($this->once())
            ->method('getValue')
            ->willReturn($price);

        $this->discountCalculatorMock->expects($this->once())
            ->method('calculateDiscount')
            ->with($this->bundleMock, $price)
            ->willReturn($price);

        $this->priceCurrencyMock->expects($this->once())
            ->method('round')
            ->with($price)
            ->willReturn($price);

        $this->bundleMock->expects($this->any())
            ->method('getPriceType')
            ->willReturn(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);

        $this->calculatorMock->expects($this->once())
            ->method('getAmount')
            ->with($price, $this->productMock, null)
            ->willReturn($amount);

        $this->assertEquals($amount, $this->selectionPrice->getAmount());
    }
}
