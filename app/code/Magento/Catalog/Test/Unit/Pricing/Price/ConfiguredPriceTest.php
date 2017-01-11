<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Pricing\Price;

use \Magento\Catalog\Pricing\Price\ConfiguredPrice;

/**
 * Test for \Magento\Catalog\Pricing\Price\ConfiguredPrice
 */
class ConfiguredPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var float
     */
    protected $basePriceValue = 800.;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $product;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $calculator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceInfo;

    /**
     * @var ConfiguredPrice
     */
    protected $model;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceCurrencyMock;

    /**
     * Initialize base dependencies
     */
    protected function setUp()
    {
        $basePrice = $this->getMock(\Magento\Framework\Pricing\Price\PriceInterface::class, [], [], '', false);
        $basePrice->expects($this->any())->method('getValue')->will($this->returnValue($this->basePriceValue));

        $this->priceInfo = $this->getMock(\Magento\Framework\Pricing\PriceInfo\Base::class, [], [], '', false);
        $this->priceInfo->expects($this->any())->method('getPrice')->will($this->returnValue($basePrice));

        $this->product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->setMethods(['getPriceInfo', 'getOptionById', 'getResource', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->once())->method('getPriceInfo')->will($this->returnValue($this->priceInfo));

        $this->item = $this->getMockBuilder(\Magento\Catalog\Model\Product\Configuration\Item\ItemInterface::class)
            ->getMock();
        $this->item->expects($this->any())->method('getProduct')->will($this->returnValue($this->product));

        $this->calculator = $this->getMock(\Magento\Framework\Pricing\Adjustment\Calculator::class, [], [], '', false);

        $this->priceCurrencyMock = $this->getMock(\Magento\Framework\Pricing\PriceCurrencyInterface::class);

        $this->model = new ConfiguredPrice($this->product, 1, $this->calculator, $this->priceCurrencyMock);
        $this->model->setItem($this->item);
    }

    /**
     * Test of value getter
     */
    public function testOptionsValueGetter()
    {
        $optionCollection = $this->getMock(
            \Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class
        );
        $optionCollection->expects($this->any())->method('getValue')->will($this->returnValue('1,2,3'));

        $optionCallback = $this->returnCallback(function ($optionId) {
            return $this->createProductOptionStub($optionId);
        });
        $this->product->expects($this->any())->method('getOptionById')->will($optionCallback);

        $itemOption = $this->getMock(\Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface::class);
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createProductOptionStub($optionId)
    {
        $option = $this->getMock(\Magento\Catalog\Model\Product\Option::class, [], [], '', false);
        $option->expects($this->any())->method('getId')->will($this->returnValue($optionId));
        $option->expects($this->atLeastOnce())->method('groupFactory')->will(
            $this->returnValue($this->createOptionTypeStub($option))
        );
        return $option;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Option $option
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createOptionTypeStub(\Magento\Catalog\Model\Product\Option $option)
    {
        $optionType = $this->getMockBuilder(\Magento\Catalog\Model\Product\Option\Type\DefaultType::class)
            ->setMethods(['setOption', 'setConfigurationItem', 'setConfigurationItemOption', 'getOptionPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionType->expects($this->atLeastOnce())->method('setOption')->with($option)->will($this->returnSelf());
        $optionType->expects($this->atLeastOnce())->method('setConfigurationItem')->will($this->returnSelf());
        $optionType->expects($this->atLeastOnce())->method('setConfigurationItemOption')->will($this->returnSelf());
        $optionType->expects($this->atLeastOnce())->method('getOptionPrice')
            ->with($this->anything(), $this->basePriceValue)
            ->will($this->returnValue(10.));
        return $optionType;
    }
}
