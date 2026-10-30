<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;
use Magento\Catalog\Pricing\Price\ConfiguredOptions;
use Magento\Catalog\Pricing\Price\ConfiguredPrice;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Pricing\Price\ConfiguredPrice
 */
class ConfiguredPriceTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var float
     */
    protected $basePriceValue = 800.;

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
     * Initialize base dependencies
     */
    protected function setUp(): void
    {

        $basePrice = $this->createMock(PriceInterface::class);
        $basePrice->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfo = $this->createMock(Base::class);
        $this->priceInfo->method('getPrice')->willReturn($basePrice);

        $this->product = $this->createPartialMock(Product::class, ['getPriceInfo', 'getOptionById', 'getResource']);
        $this->product->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfo);

        $this->item = $this->createMock(ItemInterface::class);
        $this->item->method('getProduct')->willReturn($this->product);

        $this->calculator = $this->createMock(Calculator::class);

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
    }

    /**
     * Test of value getter
     */
    public function testOptionsValueGetter()
    {
        $optionCollection = $this->createMock(
            OptionInterface::class
        );
        $optionCollection->method('getValue')->willReturn('1,2,3');

        $this->product->expects($this->any())->method('getOptionById')->willReturnCallback(function ($optionId) {
            return $this->createProductOptionStub($optionId);
        });

        $itemOption = $this->createMock(
            OptionInterface::class
        );
        $optionsList = [
            'option_1' => $itemOption,
            'option_2' => $itemOption,
            'option_3' => $itemOption,
            'option_ids' => $optionCollection,
        ];
        $this->item->expects($this->atLeastOnce())
            ->method('getOptionByCode')
            ->willReturnCallback(function ($code) use ($optionsList) {
                return $optionsList[$code];
            });
        $configuredOptions = new ConfiguredOptions(); // Use real class instead of mock
        $this->model = new ConfiguredPrice(
            $this->product,
            1,
            $this->calculator,
            $this->priceCurrencyMock,
            $this->item,
            $configuredOptions
        );
        $this->model->setItem($this->item);
        $this->assertEquals(830., $this->model->getValue());
    }

    /**
     * @param int $optionId
     * @return MockObject
     */
    protected function createProductOptionStub($optionId)
    {
        $option = $this->createMock(Option::class);
        $option->method('getId')->willReturn($optionId);
        $option->expects($this->atLeastOnce())->method('groupFactory')->willReturn(
            $this->createOptionTypeStub($option)
        );
        return $option;
    }

    /**
     * @param Option $option
     * @return MockObject
     */
    protected function createOptionTypeStub(Option $option)
    {
        $optionType = $this->createPartialMockWithReflection(
            DefaultType::class,
            ['getValue', 'setValue', 'setOption', 'getOption', 'setConfigurationItem',
             'setConfigurationItemOption', 'getOptionPrice']
        );
        $optionType->method('getValue')->willReturn(10.0);
        $optionType->method('setValue')->willReturnSelf();
        $optionType->method('setOption')->willReturnSelf();
        $optionType->method('getOption')->willReturn($option);
        $optionType->method('setConfigurationItem')->willReturnSelf();
        $optionType->method('setConfigurationItemOption')->willReturnSelf();
        $optionType->method('getOptionPrice')->willReturn(10.0);
        return $optionType;
    }
}
