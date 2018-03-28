<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class BundleOptionPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptionPrice
     */
    private $bundleOptionPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $saleableItemMock;

    /**
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptions|\PHPUnit_Framework_MockObject_MockObject
     */
    private $bundleOptionsMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->bundleOptionsMock = $this->createMock(\Magento\Bundle\Pricing\Price\BundleOptions::class);
        $this->saleableItemMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->bundleCalculatorMock = $this->createMock(\Magento\Bundle\Pricing\Adjustment\Calculator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionPrice = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Pricing\Price\BundleOptionPrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
                'bundleOptions' => $this->bundleOptionsMock
            ]
        );
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getOptions
     *
     * @return void
     */
    public function testGetOptions()
    {
        $collection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
        $this->bundleOptionsMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($collection));
        $this->assertEquals($collection, $this->bundleOptionPrice->getOptions());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getOptionSelectionAmount
     *
     * @return void
     */
    public function testGetOptionSelectionAmount()
    {
        $selectionAmount = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $selection = $this->createMock(\Magento\Bundle\Model\Selection::class);
        $this->bundleOptionsMock->expects($this->any())
            ->method('getOptionSelectionAmount')
            ->will($this->returnValue($selectionAmount))
            ->with($product, $selection, false);
        $this->assertEquals($selectionAmount, $this->bundleOptionPrice->getOptionSelectionAmount($selection));
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getAmount
     *
     * @return void
     */
    public function testGetAmount()
    {
        $amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($amountMock));
        $this->assertSame($amountMock, $this->bundleOptionPrice->getAmount());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getValue
     *
     * @return void
     */
    public function testGetValue()
    {
        $value = 1;
        $this->bundleOptionsMock->expects($this->any())->method('calculateOptions')->will($this->returnValue($value));
        $this->assertEquals($value, $this->bundleOptionPrice->getValue());
    }
}
