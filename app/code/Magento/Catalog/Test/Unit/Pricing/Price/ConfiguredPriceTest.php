<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
//        $objectManager = new ObjectManager($this);
//        $objects = [
//            [
//                ConfiguredOptions::class,
//                $this->createMock(ConfiguredOptions::class)
//            ]
//        ];
//        $objectManager->prepareObjectManager($objects);

        $basePrice = $this->getMockForAbstractClass(PriceInterface::class);
        $basePrice->expects($this->any())->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfo = $this->createMock(Base::class);
        $this->priceInfo->expects($this->any())->method('getPrice')->willReturn($basePrice);

        $this->product = $this->getMockBuilder(Product::class)
            ->onlyMethods(['getPriceInfo', 'getOptionById', 'getResource'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfo);

        $this->item = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        $this->item->expects($this->any())->method('getProduct')->willReturn($this->product);

        $this->calculator = $this->createMock(Calculator::class);

        $this->priceCurrencyMock = $this->getMockForAbstractClass(PriceCurrencyInterface::class);
    }

    /**
     * Test of value getter
     */
    public function testOptionsValueGetter()
    {
        $optionCollection = $this->createMock(
            OptionInterface::class
        );
        $optionCollection->expects($this->any())->method('getValue')->willReturn('1,2,3');

        $optionCallback = $this->returnCallback(function ($optionId) {
            return $this->createProductOptionStub($optionId);
        });
        $this->product->expects($this->any())->method('getOptionById')->will($optionCallback);

        $itemOption = $this->createMock(
            OptionInterface::class
        );
        $optionsList = [
            'option_1' => $itemOption,
            'option_2' => $itemOption,
            'option_3' => $itemOption,
            'option_ids' => $optionCollection,
        ];
        $optionsGetterByCode = $this->returnCallback(function ($code) use ($optionsList) {
            return $optionsList[$code];
        });
        $this->item->expects($this->atLeastOnce())->method('getOptionByCode')->will($optionsGetterByCode);
        $configuredOptions = $this->getMockBuilder(ConfiguredOptions::class)
                                ->disableOriginalConstructor()
                                ->onlyMethods([])
                                ->getMock();
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
        $option->expects($this->any())->method('getId')->willReturn($optionId);
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
        $optionType = $this->getMockBuilder(DefaultType::class)
            ->addMethods(['setConfigurationItem', 'setConfigurationItemOption'])
            ->onlyMethods(['setOption', 'getOptionPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionType->expects($this->atLeastOnce())->method('setOption')->with($option)->willReturnSelf();
        $optionType->expects($this->atLeastOnce())->method('setConfigurationItem')->willReturnSelf();
        $optionType->expects($this->atLeastOnce())->method('setConfigurationItemOption')->willReturnSelf();
        $optionType->expects($this->atLeastOnce())->method('getOptionPrice')
            ->with($this->anything(), $this->basePriceValue)
            ->willReturn(10.);
        return $optionType;
    }
}
