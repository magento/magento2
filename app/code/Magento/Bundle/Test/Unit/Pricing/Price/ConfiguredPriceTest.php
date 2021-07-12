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
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
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
    protected $basePriceValue = 100.;

    /**
     * @var MockObject
     */
    protected $item;

    /**
     * @var MockObject
     */
    protected $product;

    /**
     * @var MockObject
     */
    protected $calculator;

    /**
     * @var MockObject
     */
    protected $priceInfo;

    /**
     * @var ConfiguredPrice
     */
    protected $model;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    protected $priceCurrencyMock;
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
    private $discountCalculator;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $basePrice = $this->getMockForAbstractClass(PriceInterface::class);
        $basePrice->expects($this->any())->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfo = $this->createMock(Base::class);
        $this->priceInfo->expects($this->any())->method('getPrice')->willReturn($basePrice);
        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['getPriceInfo', 'getOptionById', 'getResource', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfo);
        $this->product->expects($this->any())->method('getId')->willReturn(123);

        $this->item = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        $this->item->expects($this->any())->method('getProduct')->willReturn($this->product);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);

        $this->jsonSerializerMock = $this->getMockBuilder(Json::class)
            ->getMock();
        $this->configuredPriceSelectionMock = $this->getMockBuilder(ConfiguredPriceSelection::class)
            ->setMethods(['getSelectionPriceList'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->configuredPriceSelectionMock->expects($this->any())->method('getSelectionPriceList')
            ->willReturn($this->prepareAndReturnSelectionPriceDataStub());
        $this->amountInterfaceMock = $this->getMockBuilder(AmountInterface::class)->getMock();
        $this->amountInterfaceMock->expects($this->any())->method('getBaseAmount')
            ->willReturn(100.0);
        $this->calculator = $this->getMockBuilder(Calculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->calculator->expects($this->any())->method('calculateBundleAmount')
            ->willReturn($this->amountInterfaceMock);
        $this->discountCalculator = $this->getMockBuilder(DiscountCalculator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->discountCalculator->expects($this->any())->method('calculateDiscount')
            ->willReturn(-5.0);
        $this->model = new ConfiguredPrice(
            $this->product,
            1,
            $this->calculator,
            $this->priceCurrencyMock,
            null,
            $this->jsonSerializerMock,
            $this->configuredPriceSelectionMock,
            $this->discountCalculator,
        );
        $this->model->setItem($this->item);
    }

    private function prepareAndReturnSelectionPriceDataStub()
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
     * Test of value getter
     */
    public function testGetValueMethod()
    {
        $valueFromMock = $this->model->getValue();
        $this->assertEquals(95., $valueFromMock);
    }

    /**
     * Test of value getter if no product item
     */
    public function testGetValueMethodNoItem()
    {
        unset($this->item);
        $this->product = $this->getMockBuilder(Product::class)
            //->setMethods(['getPriceInfo', 'getOptionById', 'getResource', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->item = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        $this->item->expects($this->any())->method('getProduct')->willReturn($this->product);
        $this->product->expects($this->any())->method('getId')->willReturn(false);
        $this->model->setItem($this->item);
        $valueFromMock = $this->model->getValue();
        $this->assertEquals(100., $valueFromMock);
    }
}
