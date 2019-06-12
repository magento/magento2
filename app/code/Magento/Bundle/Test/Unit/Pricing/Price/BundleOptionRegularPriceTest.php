<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
<<<<<<< HEAD

class BundleOptionRegularPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice
=======
use Magento\Bundle\Pricing\Price\BundleOptionRegularPrice;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Pricing\Adjustment\Calculator;
use Magento\Bundle\Pricing\Price\BundleOptions;
use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Bundle\Model\Selection;

/**
 * Test for Magento\Bundle\Pricing\Price\BundleRegularPrice
 */
class BundleOptionRegularPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BundleOptionRegularPrice
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $bundleOptionRegularPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
<<<<<<< HEAD
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $saleableItemMock;

    /**
<<<<<<< HEAD
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var Calculator|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $bundleCalculatorMock;

    /**
<<<<<<< HEAD
     * @var \PHPUnit_Framework_MockObject_MockObject
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

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionRegularPrice = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::class,
=======
        $this->bundleOptionsMock = $this->createMock(BundleOptions::class);
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->bundleCalculatorMock = $this->createMock(Calculator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionRegularPrice = $this->objectManagerHelper->getObject(
            BundleOptionRegularPrice::class,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::getOptions
     *
     * @return void
     */
    public function testGetOptions()
    {
<<<<<<< HEAD
        $collection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
=======
        $collection = $this->createMock(Collection::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->bundleOptionsMock->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue($collection));
        $this->assertEquals($collection, $this->bundleOptionRegularPrice->getOptions());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::getOptionSelectionAmount
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
            ->with($product, $selection, true);
        $this->assertEquals($selectionAmount, $this->bundleOptionRegularPrice->getOptionSelectionAmount($selection));
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::getAmount
     *
     * @return void
     */
    public function testGetAmount()
    {
<<<<<<< HEAD
        $amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
=======
        $amountMock = $this->createMock(AmountInterface::class);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->equalTo($this->saleableItemMock))
            ->will($this->returnValue($amountMock));
        $this->assertSame($amountMock, $this->bundleOptionRegularPrice->getAmount());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::getValue
     *
     * @return void
     */
    public function testGetValue()
    {
        $value = 1;
        $this->bundleOptionsMock->expects($this->any())->method('calculateOptions')->will($this->returnValue($value));
        $this->assertEquals($value, $this->bundleOptionRegularPrice->getValue());
    }
}
