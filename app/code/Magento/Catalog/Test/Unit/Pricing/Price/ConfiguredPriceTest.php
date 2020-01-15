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
use Magento\Catalog\Pricing\Price\ConfiguredPrice;
use Magento\Catalog\Pricing\Price\ConfiguredOptions;
use Magento\Framework\Pricing\Adjustment\Calculator;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\PriceInfo\Base;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Pricing\Price\ConfiguredPrice
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfiguredPriceTest extends TestCase
{
    /**
     * @var float
     */
    private $basePriceValue = 800.;

    /**
     * @var ConfiguredPrice
     */
    private $model;

    /**
     * @var MockObject
     */
    private $item;

    /**
     * @var MockObject
     */
    private $product;

    /**
     * @var MockObject
     */
    private $calculator;

    /**
     * @var MockObject
     */
    private $priceInfo;

    /**
     * @var PriceCurrencyInterface|MockObject
     */
    private $priceCurrencyMock;

    /**
     * @var ConfiguredOptions|MockObject
     */
    private $configuredOptions;

    /**
     * Initialize base dependencies
     */
    protected function setUp()
    {
        $basePrice = $this->createMock(PriceInterface::class);
        $basePrice->method('getValue')->willReturn($this->basePriceValue);

        $this->priceInfo = $this->createMock(Base::class);
        $this->priceInfo->method('getPrice')->willReturn($basePrice);

        $this->product = $this->getMockBuilder(Product::class)
            ->setMethods(['getPriceInfo', 'getOptionById', 'getResource', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->product->expects($this->once())->method('getPriceInfo')->willReturn($this->priceInfo);

        $this->item = $this->getMockBuilder(ItemInterface::class)
            ->getMock();
        $this->item->expects($this->any())->method('getProduct')->willReturn($this->product);

        $this->calculator = $this->createMock(Calculator::class);

        $this->priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);

        $this->configuredOptions = new ConfiguredOptions();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            ConfiguredPrice::class,
            [
                'saleableItem' => $this->product,
                'quantity' => 1,
                'calculator' => $this->calculator,
                'priceCurrency' => $this->priceCurrencyMock,
                'configuredOptions' => $this->configuredOptions
            ]
        );
        $this->model->setItem($this->item);
    }

    /**
     * Test of value getter
     */
    public function testOptionsValueGetter()
    {
        $optionCollection = $this->createMock(
            OptionInterface::class
        );
        $optionCollection->method('getValue')->will($this->returnValue('1,2,3'));

        $optionCallback = $this->returnCallback(function ($optionId) {
            return $this->createProductOptionStub($optionId);
        });
        $this->product->method('getOptionById')->will($optionCallback);

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

        $this->assertEquals(830., $this->model->getValue());
    }

    /**
     * @param int $optionId
     * @return MockObject
     */
    private function createProductOptionStub($optionId)
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
    private function createOptionTypeStub(Option $option)
    {
        $optionType = $this->getMockBuilder(DefaultType::class)
            ->setMethods(['setOption', 'setConfigurationItem', 'setConfigurationItemOption', 'getOptionPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $optionType->expects($this->atLeastOnce())->method('setOption')->with($option)->will($this->returnSelf());
        $optionType->expects($this->atLeastOnce())->method('setConfigurationItem')->will($this->returnSelf());
        $optionType->expects($this->atLeastOnce())->method('setConfigurationItemOption')->willReturnSelf();
        $optionType->expects($this->atLeastOnce())->method('getOptionPrice')
            ->with($this->anything(), $this->basePriceValue)
            ->willReturn(10.);
        return $optionType;
    }
}
