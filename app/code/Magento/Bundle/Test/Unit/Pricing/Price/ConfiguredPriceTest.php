<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $basePrice = $this->getMockForAbstractClass(PriceInterface::class);
        $basePrice->expects($this->any())->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfoMock = $this->createMock(Base::class);
        $this->priceInfoMock->expects($this->any())->method('getPrice')->willReturn($basePrice);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getPriceInfo', 'getOptionById', 'getResource', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productMock->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfoMock);
        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->jsonSerializerMock = $this->getMockBuilder(Json::class)
            ->getMock();
        $this->configuredPriceSelectionMock = $this->getMockBuilder(ConfiguredPriceSelection::class)
            ->onlyMethods(['getSelectionPriceList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configuredPriceSelectionMock->expects($this->any())->method('getSelectionPriceList')
            ->willReturn($this->prepareAndReturnSelectionPriceDataStub());
        $this->amountInterfaceMock = $this->getMockBuilder(AmountInterface::class)->getMock();
        $this->amountInterfaceMock->expects($this->any())->method('getBaseAmount')
            ->willReturn(100.00);
        $this->calculatorMock = $this->getMockBuilder(Calculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->calculatorMock->expects($this->any())->method('calculateBundleAmount')
            ->willReturn($this->amountInterfaceMock);
        $this->discountCalculatorMock = $this->getMockBuilder(DiscountCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->discountCalculatorMock->expects($this->any())->method('calculateDiscount')
            ->willReturn(-5.00);
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
        $this->productMock->expects($this->any())->method('getId')->willReturn(123);
        $this->itemMock = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        $this->itemMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);
        $this->model->setItem($this->itemMock);
        $valueFromMock = $this->model->getValue();
        $this->assertEquals(95.00, $valueFromMock);
    }

    /**
     * Test of value getter if no product item
     */
    public function testGetValueMethodNoItem(): void
    {
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->itemMock = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        $this->itemMock->expects($this->any())->method('getProduct')->willReturn($this->productMock);
        $this->productMock->expects($this->any())->method('getId')->willReturn(false);
        $this->model->setItem($this->itemMock);
        $valueFromMock = $this->model->getValue();
        $this->assertEquals(100.00, $valueFromMock);
    }

    /**
     * Stub data for calculation amount of bundle
     * @return \Magento\Framework\DataObject[]
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
}
