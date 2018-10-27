<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
<<<<<<< HEAD
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
=======

class BundleOptionRegularPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice
>>>>>>> upstream/2.2-develop
     */
    private $bundleOptionRegularPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
<<<<<<< HEAD
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Framework\Pricing\SaleableInterface|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> upstream/2.2-develop
     */
    private $saleableItemMock;

    /**
<<<<<<< HEAD
     * @var Calculator|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
>>>>>>> upstream/2.2-develop
     */
    private $bundleCalculatorMock;

    /**
<<<<<<< HEAD
     * @var BundleOptions|\PHPUnit_Framework_MockObject_MockObject
=======
     * @var \PHPUnit_Framework_MockObject_MockObject
>>>>>>> upstream/2.2-develop
     */
    private $bundleOptionsMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
<<<<<<< HEAD
        $this->bundleOptionsMock = $this->createMock(BundleOptions::class);
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->bundleCalculatorMock = $this->createMock(Calculator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionRegularPrice = $this->objectManagerHelper->getObject(
            BundleOptionRegularPrice::class,
=======
        $this->bundleOptionsMock = $this->createMock(\Magento\Bundle\Pricing\Price\BundleOptions::class);
        $this->saleableItemMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->bundleCalculatorMock = $this->createMock(\Magento\Bundle\Pricing\Adjustment\Calculator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionRegularPrice = $this->objectManagerHelper->getObject(
            \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::class,
>>>>>>> upstream/2.2-develop
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
<<<<<<< HEAD
                'bundleOptions' => $this->bundleOptionsMock,
=======
                'bundleOptions' => $this->bundleOptionsMock
>>>>>>> upstream/2.2-develop
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
        $collection = $this->createMock(Collection::class);
=======
        $collection = $this->createMock(\Magento\Bundle\Model\ResourceModel\Option\Collection::class);
>>>>>>> upstream/2.2-develop
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
        $selectionAmount = $this->createMock(AmountInterface::class);
        $product = $this->createMock(Product::class);
        $selection = $this->createMock(Selection::class);
=======
        $selectionAmount = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $selection = $this->createMock(\Magento\Bundle\Model\Selection::class);
>>>>>>> upstream/2.2-develop
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
        $amountMock = $this->createMock(AmountInterface::class);
=======
        $amountMock = $this->createMock(\Magento\Framework\Pricing\Amount\AmountInterface::class);
>>>>>>> upstream/2.2-develop
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
