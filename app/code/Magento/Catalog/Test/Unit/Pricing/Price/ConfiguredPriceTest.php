<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use Magento\Catalog\Pricing\Price\ConfiguredPrice;

/**
 * Test for \Magento\Catalog\Pricing\Price\ConfiguredPrice
 */
class ConfiguredPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var float
     */
    protected $basePriceValue = 800.;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $item;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $product;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $calculator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceInfo;

    /**
     * @var ConfiguredPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Initialize base dependencies
     */
    protected function setUp(): void
    {
        $basePrice = $this->createMock(\Magento\Framework\Pricing\Price\PriceInterface::class);
        $basePrice->expects($this->any())->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfo = $this->createMock(\Magento\Framework\Pricing\PriceInfo\Base::class);
        $this->priceInfo->expects($this->any())->method('getPrice')->willReturn($basePrice);

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getPriceInfo', 'getOptionById', 'getResource', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfo);

        $this->item = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class)
            ->getMock();
        $this->item->expects($this->any())->method('getProduct')->willReturn($this->product);

        $this->calculator = $this->createMock(\Magento\Framework\Pricing\Adjustment\Calculator::class);

        $this->priceCurrencyMock = $this->createMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->model = new ConfiguredPrice($this->product, 1, $this->calculator, $this->priceCurrencyMock);
        $this->model->setItem($this->item);
    }

    /**
     * Test of value getter
     */
    public function testOptionsValueGetter()
    {
        $optionCollection = $this->createMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
        );
        $optionCollection->expects($this->any())->method('getValue')->willReturn('1,2,3');

        $optionCallback = $this->returnCallback(function ($optionId) {
            return $this->createProductOptionStub($optionId);
        });
        $this->product->expects($this->any())->method('getOptionById')->will($optionCallback);

        $itemOption = $this->createMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
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

        $this->assertEquals(830., $this->model->getValue());
    }

    /**
     * @param int $optionId
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createProductOptionStub($optionId)
    {
        $option = $this->createMock(\Magento\Catalog\Model\Product\Option::class);
        $option->expects($this->any())->method('getId')->willReturn($optionId);
        $option->expects($this->atLeastOnce())->method('groupFactory')->willReturn(
            $this->createOptionTypeStub($option)
        );
        return $option;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function createOptionTypeStub(\Magento\Catalog\Model\Product\Option $option)
    {
        $optionType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(['setOption', 'setConfigurationItem', 'setConfigurationItemOption', 'getOptionPrice'])
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
