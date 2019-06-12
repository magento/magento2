<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Pricing\Price\BundleOptions;
use Magento\Bundle\Pricing\Adjustment\Calculator;
use \Magento\Bundle\Model\Selection;

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
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Price\BundleOptions|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var BundleOptions|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $bundleOptionsMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->bundleOptionsMock = $this->createMock(\Magento\Bundle\Pricing\Price\BundleOptions::class);
        $this->saleableItemMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->bundleCalculatorMock = $this->createMock(\Magento\Bundle\Pricing\Adjustment\Calculator::class);
=======
        $this->bundleOptionsMock = $this->createMock(BundleOptions::class);
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->bundleCalculatorMock = $this->createMock(Calculator::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionPrice = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Pricing\Price\BundleOptionPrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
<<<<<<< HEAD
                'bundleOptions' => $this->bundleOptionsMock
=======
                'bundleOptions' => $this->bundleOptionsMock,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
<<<<<<< HEAD
        $selectionAmount = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $selection = $this->createMock(\Magento\Bundle\Model\Selection::class);
=======
        $selectionAmount = $this->createMock(AmountInterface::class);
        $product = $this->createMock(Product::class);
        $selection = $this->createMock(Selection::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
        $amountMock = $this->createMock(AmountInterface::class);
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
