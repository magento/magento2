<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Pricing\Price\DiscountCalculator;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Bundle\Pricing\Price\ConfiguredPrice;
use Magento\Bundle\Pricing\Adjustment\Calculator;
use Magento\Catalog\Pricing\Price\ConfiguredPriceSelection;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Bundle\Pricing\Price\ConfiguredPrice
 */
class ConfiguredPriceTest extends TestCase
{
    /**
     * @var float
     */
    private $basePriceValue = 100.00;

    /**
     * @var ItemInterface|MockObject
     */
    private $itemMock;

    /**
     * @var Product|MockObject
     */
    private $productMock;

    /**
     * @var Calculator|MockObject
     */
    private $calculatorMock;

    /**
     * @var Base|MockObject
     */
    private $priceInfoMock;

    /**
     * @var ConfiguredPrice
     */
    private $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var ConfiguredPriceSelection|MockObject
     */
    private $configuredPriceSelectionMock;

    /**
     * @var AmountInterface|MockObject
     */
    private $amountInterfaceMock;

    /**
     * @var DiscountCalculator|MockObject
     */
    private $discountCalculatorMock;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $basePrice = $this->createMock(PriceInterface::class);
        $basePrice->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfoMock = $this->createMock(Base::class);
        $this->priceInfoMock->method('getPrice')->willReturn($basePrice);
        $this->productMock = $this->createPartialMock(
            Product::class,
            ['getPriceInfo', 'getOptionById', 'getResource', 'getId']
        );
        $this->productMock->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfoMock);
        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->jsonSerializerMock = $this->createMock(Json::class);
        $this->configuredPriceSelectionMock = $this->createPartialMock(
            ConfiguredPriceSelection::class,
            ['getSelectionPriceList']
        );
        $this->configuredPriceSelectionMock->method('getSelectionPriceList')
            ->willReturn($this->prepareAndReturnSelectionPriceDataStub());
        $this->amountInterfaceMock = $this->createAmountInterfaceMock();
        $this->amountInterfaceMock->method('getBaseAmount')->willReturn(100.00);
        $this->calculatorMock = $this->createMock(Calculator::class);
        $this->calculatorMock->method('calculateBundleAmount')->willReturn($this->amountInterfaceMock);
        $this->discountCalculatorMock = $this->createMock(DiscountCalculator::class);
        $this->discountCalculatorMock->method('calculateDiscount')->willReturn(-5.00);
        $this->model = new ConfiguredPrice(
            $this->productMock,
            1,
            $this->calculatorMock,
            $this->priceCurrencyMock,
            null,
            $this->jsonSerializerMock,
            $this->configuredPriceSelectionMock,
            $this->discountCalculatorMock,
        );
    }

    /**
     * Test of value getter when item presented
     */
    public function testGetValueMethod(): void
    {
        $this->productMock->method('getId')->willReturn(123);
        $this->itemMock = $this->createMock(ItemInterface::class);
        $this->itemMock->method('getProduct')->willReturn($this->productMock);
        $this->model->setItem($this->itemMock);
        $valueFromMock = $this->model->getValue();
        $this->assertEquals(95.00, $valueFromMock);
    }

    /**
     * Test of value getter if no product item
     */
    public function testGetValueMethodNoItem(): void
    {
        $this->productMock = $this->createMock(Product::class);
        $this->itemMock = $this->createMock(ItemInterface::class);
        $this->itemMock->method('getProduct')->willReturn($this->productMock);
        $this->productMock->method('getId')->willReturn(false);
        $this->model->setItem($this->itemMock);
        $valueFromMock = $this->model->getValue();
        $this->assertEquals(100.00, $valueFromMock);
    }

    /**
     * Stub data for calculation amount of bundle
     * @return DataObject[]
     */
    private function prepareAndReturnSelectionPriceDataStub(): array
    {
        $first = new DataObject();
        $first->setValue(2);
        $first->setQuantity(1);
        $second = new DataObject();
        $second->setValue(3);
        $second->setQuantity(1);
        return [
            $first,
            $second
        ];
    }

    /**
     * Create a mock that implements all AmountInterface abstract methods
     *
     * @return AmountInterface
     */
    private function createAmountInterfaceMock(): AmountInterface
    {
        $mock = $this->createMock(AmountInterface::class);
        
        // Mock all abstract methods with default values
        $mock->method('__toString')->willReturn('0');
        $mock->method('getAdjustmentAmount')->willReturn(0.0);
        $mock->method('getTotalAdjustmentAmount')->willReturn(0.0);
        $mock->method('getAdjustmentAmounts')->willReturn([]);
        $mock->method('hasAdjustment')->willReturn(false);
        
        return $mock;
    }
}
