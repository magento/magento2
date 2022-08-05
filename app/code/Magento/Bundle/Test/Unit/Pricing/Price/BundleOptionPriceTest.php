<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Pricing\Price;

use Magento\Bundle\Model\ResourceModel\Option\Collection;
use Magento\Bundle\Model\Selection;
use Magento\Bundle\Pricing\Adjustment\BundleCalculatorInterface;
use Magento\Bundle\Pricing\Adjustment\Calculator;
use Magento\Bundle\Pricing\Price\BundleOptionPrice;
use Magento\Bundle\Pricing\Price\BundleOptions;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\Amount\AmountInterface;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BundleOptionPriceTest extends TestCase
{
    /**
     * @var BundleOptionPrice
     */
    private $bundleOptionPrice;

    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var SaleableInterface|MockObject
     */
    private $saleableItemMock;

    /**
     * @var BundleCalculatorInterface|MockObject
     */
    private $bundleCalculatorMock;

    /**
     * @var BundleOptions|MockObject
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
        $this->bundleOptionPrice = $this->objectManagerHelper->getObject(
            BundleOptionPrice::class,
            [
                'saleableItem' => $this->saleableItemMock,
                'quantity' => 1.,
                'calculator' => $this->bundleCalculatorMock,
                'bundleOptions' => $this->bundleOptionsMock,
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
        $collection = $this->createMock(Collection::class);
        $this->bundleOptionsMock->expects($this->any())
            ->method('getOptions')
            ->willReturn($collection);
        $this->assertEquals($collection, $this->bundleOptionPrice->getOptions());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getOptionSelectionAmount
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
        $amountMock = $this->getMockForAbstractClass(AmountInterface::class);
        $this->bundleCalculatorMock->expects($this->once())
            ->method('getOptionsAmount')
            ->with($this->saleableItemMock)
            ->willReturn($amountMock);
        $this->assertSame($amountMock, $this->bundleOptionPrice->getAmount());
    }

    /**
     * Test method \Magento\Bundle\Pricing\Price\BundleOptionPrice::getValue
     *
     * @return void
     */
    public function testGetValue()
    {
        $value = 1.0;
        $this->bundleOptionsMock->expects($this->any())->method('calculateOptions')->willReturn($value);
        $this->assertEquals($value, $this->bundleOptionPrice->getValue());
    }
}
