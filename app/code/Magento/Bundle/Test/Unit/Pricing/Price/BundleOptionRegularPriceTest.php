<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
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
     */
    private $bundleOptionRegularPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var Product|\PHPUnit\Framework\MockObject\MockObject
     */
    private $saleableItemMock;

    /**
     * @var Calculator|\PHPUnit\Framework\MockObject\MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var BundleOptions|\PHPUnit\Framework\MockObject\MockObject
     */
    private $bundleOptionsMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->bundleOptionsMock = $this->createMock(BundleOptions::class);
        $this->saleableItemMock = $this->createMock(Product::class);
        $this->bundleCalculatorMock = $this->createMock(Calculator::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->bundleOptionRegularPrice = $this->objectManagerHelper->getObject(
            BundleOptionRegularPrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
                'bundleOptions' => $this->bundleOptionsMock,
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
        $collection = $this->createMock(Collection::class);
        $this->bundleOptionsMock->expects($this->any())
            ->method('getOptions')
            ->willReturn($collection);
        $this->assertEquals($collection, $this->bundleOptionRegularPrice->getOptions());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionRegularPrice::getOptionSelectionAmount
     *
     * @return void
     */
    public function testGetOptionSelectionAmount()
    {
        $selectionAmount = $this->getMockForAbstractClass(AmountInterface::class);
        $product = $this->createMock(Product::class);
        $selection = $this->createMock(Selection::class);
        $this->bundleOptionsMock->expects($this->any())
            ->method('getOptionSelectionAmount')
            ->willReturn($selectionAmount)
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
        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->equalTo($this->saleableItemMock))
            ->willReturn($amountMock);
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
        $this->bundleOptionsMock->expects($this->any())->method('calculateOptions')->willReturn($value);
        $this->assertEquals($value, $this->bundleOptionRegularPrice->getValue());
    }
}
